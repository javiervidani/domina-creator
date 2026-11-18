<?php

namespace Tests\Feature;

use App\Content\VersionRecorder;
use App\Models\Content;
use App\Tenancy\Provisioner;
use App\Tenancy\Tenancy;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

// PostgreSQL only. Own throwaway creator (900040).
class EditorTest extends TestCase
{
    private const ID = 900040;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('PostgreSQL only.');
        }
        app(Provisioner::class)->provision(self::ID);
        app(Tenancy::class)->run(self::ID, fn () => Content::withTrashed()->forceDelete());
    }

    public function test_quick_edits_share_one_history_entry_and_deliberate_actions_get_their_own(): void
    {
        app(Tenancy::class)->run(self::ID, function () {
            $c = Content::create(['title' => 'T', 'master_text' => 'one']);
            $r = app(VersionRecorder::class);

            $r->record($c, 'one a');
            $r->record($c, 'one ab');
            $this->assertSame(1, $c->versions()->count());
            $this->assertSame('one ab', $c->versions()->first()->text);

            $r->record($c, 'new text', 'Took the new version');
            $this->assertSame(2, $c->versions()->count());
        });
    }
}
