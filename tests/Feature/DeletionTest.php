<?php

namespace Tests\Feature;

use App\Models\Creator;
use App\Models\CreatorMember;
use App\Models\DeletionRequest;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\CreatorDeletion;
use App\Tenancy\Provisioner;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

// PostgreSQL only, and the app_user plus the lock/purge functions must exist (central migrations run as the admin).
// It makes its own creator and user, and removes anything left over.
class DeletionTest extends TestCase
{
    private Creator $creator;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('PostgreSQL only.');
        }
        $this->user = User::create(['name' => 'Del', 'email' => 'del'.uniqid().'@example.test', 'password' => 'a-long-password']);
        $this->creator = Creator::create(['name' => 'Del']);
        CreatorMember::create(['user_id' => $this->user->id, 'creator_id' => $this->creator->id]);
        app(Provisioner::class)->provision($this->creator->id);
    }

    protected function tearDown(): void
    {
        if (DB::getDriverName() === 'pgsql' && isset($this->creator) && Creator::whereKey($this->creator->id)->exists()) {
            Tenant::where('creator_id', $this->creator->id)->update(['status' => 'pending_deletion', 'purge_at' => now()->subMinute()]);
            app(CreatorDeletion::class)->purgeDue();
        }
        parent::tearDown();
    }

    private function enter(): void
    {
        DB::statement('SET ROLE "creator_'.$this->creator->id.'"');
        DB::statement('RESET ROLE');
    }

    public function test_the_functions_refuse_a_creator_that_is_not_being_deleted(): void
    {
        foreach (['lock_creator', 'unlock_creator', 'purge_creator'] as $fn) {
            try {
                DB::select("select public.{$fn}(?)", [$this->creator->id]);
                $this->fail("{$fn} should refuse an active creator");
            } catch (QueryException $e) {
                $this->assertStringContainsString('not in the right state', $e->getMessage());
            }
        }
    }

    public function test_request_locks_the_schema_and_restore_opens_it_again(): void
    {
        $deletion = app(CreatorDeletion::class);
        $deletion->request($this->creator->id, $this->user->id);

        $tenant = Tenant::where('creator_id', $this->creator->id)->first();
        $this->assertSame('pending_deletion', $tenant->status);
        $this->assertSame(CreatorDeletion::DAYS, (int) now()->diffInDays($tenant->purge_at, true) + 1);

        try {
            $this->enter();
            $this->fail('A locked schema must not be enterable.');
        } catch (QueryException $e) {
            $this->assertStringContainsString('permission denied', $e->getMessage());
        }

        $deletion->restore($this->creator->id);
        $this->enter(); // no exception: the membership is back
        $this->assertSame('active', Tenant::where('creator_id', $this->creator->id)->value('status'));
        $this->assertNotNull(DeletionRequest::where('creator_id', $this->creator->id)->value('restored_at'));
    }

    public function test_purge_waits_for_day_60_then_drops_everything_and_keeps_a_minimal_record(): void
    {
        $deletion = app(CreatorDeletion::class);
        $deletion->request($this->creator->id, $this->user->id);

        $this->assertSame(0, $deletion->purgeDue()); // not due yet

        Tenant::where('creator_id', $this->creator->id)->update(['purge_at' => now()->subMinute()]);
        $this->assertSame(1, $deletion->purgeDue());

        $this->assertFalse(DB::selectOne("select exists(select 1 from pg_namespace where nspname = 'creator_{$this->creator->id}') as e")->e);
        $this->assertFalse(DB::selectOne("select exists(select 1 from pg_roles where rolname = 'creator_{$this->creator->id}') as e")->e);
        $this->assertFalse(Creator::whereKey($this->creator->id)->exists());
        $this->assertFalse(User::whereKey($this->user->id)->exists());
        $this->assertNotNull(DeletionRequest::where('creator_id', $this->creator->id)->value('purged_at'));
    }
}
