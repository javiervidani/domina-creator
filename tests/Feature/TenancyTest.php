<?php

namespace Tests\Feature;

use App\Jobs\CreatorJob;
use App\Tenancy\Provisioner;
use App\Tenancy\Tenancy;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Tests\TestCase;

// Needs the real PostgreSQL setup (app_user plus the provision_creator function), so it is skipped on sqlite.
// Run with: DB_CONNECTION=pgsql php artisan test --filter=TenancyTest, after migrating with pgsql_admin.
class TenancyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('PostgreSQL only.');
        }
    }

    public function test_creators_cannot_read_each_other_and_unswitched_access_is_denied(): void
    {
        [$a, $b] = [900001, 900002];
        app(Provisioner::class)->provision($a);
        app(Provisioner::class)->provision($b);
        $tenancy = app(Tenancy::class);

        $tenancy->run($a, fn () => DB::table('activity_log')->insert(['event' => 'a', 'created_at' => now()]));

        $this->assertSame(0, $tenancy->run($b, fn () => DB::table('activity_log')->count()));
        $this->assertDenied(fn () => $tenancy->run($b, fn () => DB::select('select * from creator_900001.activity_log')));
        $this->assertDenied(fn () => DB::select('select * from creator_900001.activity_log'));
        $this->assertDenied(fn () => $tenancy->run($b, fn () => DB::select('select * from public.users')));
    }

    public function test_a_job_runs_inside_its_creator_schema_and_resets_afterwards(): void
    {
        app(Provisioner::class)->provision(900003);

        $job = new class(900003) extends CreatorJob {
            public static ?string $path = null;

            protected function work(): void
            {
                self::$path = DB::selectOne('show search_path')->search_path;
            }
        };
        $job->handle(app(Tenancy::class));

        $this->assertSame('creator_900003', $job::$path);
        $this->assertNull(app(Tenancy::class)->current());
    }

    private function assertDenied(callable $fn): void
    {
        try {
            $fn();
            $this->fail('Expected permission denied.');
        } catch (QueryException $e) {
            $this->assertStringContainsString('permission denied', $e->getMessage());
        }
    }
}
