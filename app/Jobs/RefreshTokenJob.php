<?php

namespace App\Jobs;

use App\Models\ConnectedAccount;
use App\Models\SyncRun;
use App\Platforms\Instagram\InstagramConnector;
use App\Platforms\PlatformException;

class RefreshTokenJob extends CreatorJob
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

        $run = SyncRun::create(['connected_account_id' => $account->id, 'kind' => 'token_refresh',
            'status' => 'running', 'started_at' => now()]);

        try {
            $new = (new InstagramConnector($account->token->access_token))->refreshToken();
            $account->token->update(['access_token' => $new['access_token'],
                'expires_at' => now()->addSeconds($new['expires_in']), 'refreshed_at' => now()]);
            $account->update(['status' => 'ok', 'last_error' => null]);
            $run->update(['status' => 'ok', 'finished_at' => now()]);
        } catch (PlatformException $e) {
            // The page shows this error; a dead token needs the creator to reconnect.
            $account->update(['last_error' => $e->getMessage(), 'status' => $e->needsReconnect ? 'reconnect' : $account->status]);
            $run->update(['status' => 'failed', 'error' => $e->getMessage(), 'finished_at' => now()]);
        }
    }
}
