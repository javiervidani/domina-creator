<?php

namespace App\Jobs;

use App\Models\ConnectedAccount;
use App\Models\ContentCopy;
use App\Models\PublishJob;
use App\Platforms\PlatformException;
use App\Publishing\Publisher;
use App\Publishing\TargetText;
use Illuminate\Support\Facades\Log;

// Sends or re-sends one target copy. Temporary failures (rate limit, Telegram down) are retried; anything else stays
// failed on the copy with the reason, and the creator presses Retry.
class PublishCopyJob extends CreatorJob
{
    public int $tries = 3;

    public array $backoff = [30, 120];

    public function __construct(int $creatorId, public int $copyId)
    {
        parent::__construct($creatorId);
    }

    protected function work(): void
    {
        $copy = ContentCopy::with('content.targetTexts')->find($this->copyId);
        if (! $copy || $copy->link_mode !== 'target') {
            return;
        }

        $account = ConnectedAccount::with('token')->find($copy->connected_account_id);
        $log = PublishJob::create(['content_copy_id' => $copy->id, 'status' => 'running',
            'kind' => str_starts_with($copy->external_id, 'pending:') ? 'publish' : 'republish']);

        if (! $account || ! $account->token) {
            $copy->update(['status' => 'failed', 'last_error' => 'The account is not connected any more.']);
            $log->update(['status' => 'failed', 'error' => 'account missing']);

            return;
        }

        try {
            app(Publisher::class)->publish($copy, $account, TargetText::for($copy->content, $copy->platform));
            $log->update(['status' => 'sent']);
        } catch (PlatformException $e) {
            $copy->update(['status' => 'failed', 'last_error' => $e->getMessage()]);
            $log->update(['status' => 'failed', 'error' => $e->getMessage()]);
            Log::warning("Publish failed (creator {$this->creatorId}, copy {$copy->id}): ".$e->getMessage());
            if ($e->needsReconnect) {
                $account->update(['status' => 'reconnect', 'last_error' => $e->getMessage()]);
            }
            if ($e->retryable && $this->attempts() < $this->tries) {
                throw $e;
            }
        }
    }
}
