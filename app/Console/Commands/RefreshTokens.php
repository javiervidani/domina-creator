<?php

namespace App\Console\Commands;

use App\Jobs\RefreshTokenJob;
use App\Models\AccountToken;
use App\Models\Tenant;
use App\Tenancy\Tenancy;
use Illuminate\Console\Command;

class RefreshTokens extends Command
{
    protected $signature = 'accounts:refresh-tokens';

    protected $description = 'Queue a token refresh for every account whose token is under 21 days from expiry';

    public function handle(Tenancy $tenancy): int
    {
        foreach (Tenant::where('status', 'active')->pluck('creator_id') as $creatorId) {
            // Read inside the schema, dispatch outside it: the jobs table is in public.
            $ids = $tenancy->run($creatorId, fn () => AccountToken::query()
                ->where('expires_at', '<', now()->addDays(21))
                ->where(fn ($q) => $q->whereNull('refreshed_at')->orWhere('refreshed_at', '<', now()->subDay()))
                ->pluck('connected_account_id'));

            foreach ($ids as $accountId) {
                RefreshTokenJob::dispatch($creatorId, $accountId);
            }
        }

        return self::SUCCESS;
    }
}
