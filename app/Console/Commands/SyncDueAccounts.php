<?php

namespace App\Console\Commands;

use App\Import\ImportSchedule;
use App\Jobs\SyncAccountJob;
use App\Models\ConnectedAccount;
use App\Models\Tenant;
use App\Tenancy\Tenancy;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SyncDueAccounts extends Command
{
    protected $signature = 'accounts:sync-due';

    protected $description = 'Queue an import for every account that has import on and is due by its frequency';

    public function handle(Tenancy $tenancy): int
    {
        foreach (Tenant::where('status', 'active')->pluck('creator_id') as $creatorId) {
            // Read inside the schema, dispatch outside it: the jobs table is in public.
            $ids = $tenancy->run($creatorId, fn () => ConnectedAccount::where('platform', 'instagram')->where('import_enabled', true)->where('status', 'ok')->get()
                ->filter(fn ($a) => ImportSchedule::isDue($a->import_frequency, $a->last_sync_at, now()))->pluck('id'));

            foreach ($ids as $accountId) {
                // Until the job has run, last_sync_at is still old; this keeps one job queued per account at a time.
                if (Cache::add("sync-queued:{$creatorId}:{$accountId}", 1, 600)) {
                    SyncAccountJob::dispatch($creatorId, $accountId);
                }
            }
        }

        return self::SUCCESS;
    }
}
