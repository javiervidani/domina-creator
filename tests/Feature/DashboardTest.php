<?php

namespace Tests\Feature;

use App\Dashboard\Attention;
use App\Dashboard\Onboarding;
use App\Models\AccountTarget;
use App\Models\ConnectedAccount;
use App\Models\Content;
use App\Models\ContentCopy;
use App\Tenancy\Provisioner;
use App\Tenancy\Tenancy;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

// PostgreSQL only. Own throwaway creator (900060). Routes are named by the app, so route() works without a request.
class DashboardTest extends TestCase
{
    private const ID = 900060;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('PostgreSQL only.');
        }
        app(Provisioner::class)->provision(self::ID);
        app(Tenancy::class)->run(self::ID, function () {
            Content::withTrashed()->forceDelete();
            ConnectedAccount::query()->delete();
        });
    }

    public function test_wizard_steps_follow_the_data(): void
    {
        app(Tenancy::class)->run(self::ID, function () {
            $this->assertSame([false, false, false, false], array_column(Onboarding::steps(false), 'done'));

            $ig = ConnectedAccount::create(['platform' => 'instagram', 'external_id' => 'i', 'handle' => 'ig', 'is_source_of_truth' => true]);
            $this->assertSame([true, true, false, false], array_column(Onboarding::steps(false), 'done'));
            $this->assertSame([true, true, true, false], array_column(Onboarding::steps(true), 'done')); // skipped targets

            Content::create(['title' => 'T', 'master_text' => 'x']);
            $this->assertTrue(Onboarding::complete(true));
        });
    }

    public function test_attention_lists_drafts_failures_changes_and_dead_tokens(): void
    {
        app(Tenancy::class)->run(self::ID, function () {
            $ig = ConnectedAccount::create(['platform' => 'instagram', 'external_id' => 'i', 'handle' => 'ig', 'status' => 'reconnect']);
            $tg = ConnectedAccount::create(['platform' => 'telegram', 'external_id' => 't', 'handle' => 'chan']);
            $c = Content::create(['title' => 'T', 'master_text' => 'x', 'source_changed' => true]);
            foreach (['draft' => 'd', 'failed' => 'f'] as $status => $ext) {
                ContentCopy::create(['content_id' => $c->id, 'connected_account_id' => $tg->id, 'platform' => 'telegram' , 'external_id' => $ext,
                    'source_text' => 'x', 'source_hash' => 'h', 'match_hash' => 'h', 'link_mode' => 'target', 'status' => $status, 'last_error' => 'boom']);
            }

            $kinds = array_column(Attention::items(), 'kind');

            $this->assertEqualsCanonicalizing(['account', 'failed', 'draft', 'changed'], $kinds);
        });
    }
}
