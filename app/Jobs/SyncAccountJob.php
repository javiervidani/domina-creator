<?php

namespace App\Jobs;

use App\Import\AccountImporter;
use App\Models\ConnectedAccount;
use App\Models\SyncRun;
use App\Platforms\Instagram\InstagramConnector;
use App\Platforms\PlatformException;
use Illuminate\Support\Facades\Log;

// Pulls an account's posts into the Library and records the run. Transient errors are retried by the queue.
class SyncAccountJob extends CreatorJob
{
    public int $tries = 3;

    public array $backoff = [60, 300];

    public function __construct(int $creatorId, public int $accountId)
    {
        parent::__construct($creatorId);
    }

    protected function work(): void
    {
        $account = ConnectedAccount::with('token')->find($this->accountId);
        if (! $account || ! $account->token) {
            return;
        }

        $run = SyncRun::create(['connected_account_id' => $account->id, 'kind' => 'sync',
            'status' => 'running', 'started_at' => now()]);

        try {
            $counts = app(AccountImporter::class)->run($account, new InstagramConnector($account->token->access_token));
            $run->update(['status' => 'ok', 'fetched' => $counts['seen'], 'created' => $counts['created'] + $counts['linked'],
                'changed' => $counts['changed'], 'finished_at' => now()]);
            $account->update(['last_sync_at' => now(), 'last_error' => null]);
        } catch (PlatformException $e) {
            // Also in the log so /ops/logs shows it. Account id and creator only; the message never holds the token.
            Log::warning('Instagram sync failed'." (creator {$this->creatorId}, account {$account->id}): ".$e->getMessage());
            $account->update(['last_error' => $e->getMessage(), 'status' => $e->needsReconnect ? 'reconnect' : $account->status]);
            $run->update(['status' => 'failed', 'error' => $e->getMessage(), 'finished_at' => now()]);

            if (! $e->needsReconnect) {
                throw $e; // a dead token needs the creator, anything else is worth another try
            }
        }
    }
}
