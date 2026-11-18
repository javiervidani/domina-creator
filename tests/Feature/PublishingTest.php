<?php

namespace Tests\Feature;

use App\Jobs\PublishCopyJob;
use App\Models\AccountTarget;
use App\Models\ConnectedAccount;
use App\Models\Content;
use App\Models\ContentCopy;
use App\Publishing\TargetPlanner;
use App\Tenancy\Provisioner;
use App\Tenancy\Tenancy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

// PostgreSQL only. Own throwaway creator (900050), emptied before each test. Telegram is faked.
class PublishingTest extends TestCase
{
    private const ID = 900050;

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

    private function accounts(string $mode = 'review'): array
    {
        $ig = ConnectedAccount::create(['platform' => 'instagram', 'external_id' => 'i', 'handle' => 'ig']);
        $tg = ConnectedAccount::create(['platform' => 'telegram', 'external_id' => '-100', 'handle' => 'chan']);
        $tg->token()->create(['access_token' => 'tok']);
        AccountTarget::create(['source_account_id' => $ig->id, 'target_account_id' => $tg->id, 'mode' => $mode]);

        return [$ig, $tg, Content::create(['title' => 'T', 'master_text' => 'Hello'])];
    }

    public function test_review_first_makes_a_draft_and_auto_queues_it(): void
    {
        app(Tenancy::class)->run(self::ID, function () {
            [$ig, , $c] = $this->accounts('review');
            $this->assertSame([], app(TargetPlanner::class)->planForNew([$c->id], $ig));
            $this->assertSame('draft', ContentCopy::where('link_mode', 'target')->first()->status);

            ContentCopy::query()->delete();
            AccountTarget::query()->update(['mode' => 'auto']);
            $this->assertCount(1, app(TargetPlanner::class)->planForNew([$c->id], $ig));
        });
    }

    public function test_publish_then_republish_edits_the_same_message_and_failures_show(): void
    {
        Http::fake(['api.telegram.org/*sendMessage' => Http::response(['ok' => true, 'result' => ['message_id' => 5]]),
            'api.telegram.org/*editMessageText' => Http::response(['ok' => true, 'result' => []])]);
        $copyId = app(Tenancy::class)->run(self::ID, function () {
            [, $tg, $c] = $this->accounts();

            return app(TargetPlanner::class)->draftFor($c, $tg)->id;
        });

        (new PublishCopyJob(self::ID, $copyId))->handle(app(Tenancy::class));
        app(Tenancy::class)->run(self::ID, function () use ($copyId) {
            $copy = ContentCopy::find($copyId);
            $this->assertSame('published', $copy->status);
            $this->assertSame('-100:5', $copy->external_id);
            $copy->content->update(['master_text' => 'Hello, edited']);
        });

        (new PublishCopyJob(self::ID, $copyId))->handle(app(Tenancy::class));
        Http::assertSent(fn ($r) => str_contains($r->url(), 'editMessageText') && $r['message_id'] === 5 && $r['text'] === 'Hello, edited');

        Http::fake(['api.telegram.org/*' => Http::response(['ok' => false, 'error_code' => 400, 'description' => 'chat not found'], 400)]);
        (new PublishCopyJob(self::ID, $copyId))->handle(app(Tenancy::class));
        app(Tenancy::class)->run(self::ID, function () use ($copyId) {
            $copy = ContentCopy::find($copyId);
            $this->assertSame('failed', $copy->status);
            $this->assertSame('chat not found', $copy->last_error);
        });
    }
}
