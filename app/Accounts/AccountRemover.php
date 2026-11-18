<?php

namespace App\Accounts;

use App\Models\ConnectedAccount;
use App\Models\Tenant;
use App\Tenancy\Tenancy;

// Meta calls us without a session, so it only knows the Instagram user id. Look in every active creator schema.
// The token row goes with the account (cascade). Imported content stays: this removes the connection, not the history.
class AccountRemover
{
    public function __construct(private Tenancy $tenancy)
    {
    }

    public function removeByExternalId(string $platform, string $externalId): int
    {
        $removed = 0;

        foreach (Tenant::where('status', 'active')->pluck('creator_id') as $creatorId) {
            $removed += $this->tenancy->run($creatorId, fn () => ConnectedAccount::where('platform', $platform)
                ->where('external_id', $externalId)->get()->each->delete()->count());
        }

        return $removed;
    }
}
