<?php

namespace App\Jobs;

use App\Models\ConnectedAccount;
use App\Models\SyncRun;
use App\Platforms\Instagram\InstagramConnector;
use App\Platforms\PlatformException;
use Illuminate\Support\Facades\Log;

// Step 4 only proves the connection works: it reads one page and records the run. Storing content is step 5.
class SyncAccountJob extends CreatorJob
{
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
            $page = (new InstagramConnector($account->token->access_token))->mediaPage();
            $run->update(['status' => 'ok', 'fetched' => count($page['items']), 'finished_at' => now()]);
            $account->update(['last_sync_at' => now(), 'last_error' => null]);
        } catch (PlatformException $e) {
            // Also in the log so /ops/logs shows it. Account id and creator only; the message never holds the token.
            Log::warning('Instagram sync failed'." (creator {$this->creatorId}, account {$account->id}): ".$e->getMessage());
            $account->update(['last_error' => $e->getMessage(), 'status' => $e->needsReconnect ? 'reconnect' : $account->status]);
            $run->update(['status' => 'failed', 'error' => $e->getMessage(), 'finished_at' => now()]);
        }
    }
}
