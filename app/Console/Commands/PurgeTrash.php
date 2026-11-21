<?php

namespace App\Console\Commands;

use App\Media\ImageKeeper;
use App\Models\Collection;
use App\Models\Content;
use App\Models\Tenant;
use App\Tenancy\CreatorDeletion;
use App\Tenancy\Tenancy;
use Illuminate\Console\Command;

class PurgeTrash extends Command
{
    protected $signature = 'items:purge';

    protected $description = 'Remove posts and collections that have been in the Recycle bin for 60 days';

    public function handle(Tenancy $tenancy): int
    {
        $cutoff = now()->subDays(CreatorDeletion::DAYS);

        foreach (Tenant::where('status', 'active')->pluck('creator_id') as $creatorId) {
            $tenancy->run($creatorId, function () use ($cutoff, $creatorId) {
                Content::onlyTrashed()->where('deleted_at', '<', $cutoff)->forceDelete();
                Collection::onlyTrashed()->where('deleted_at', '<', $cutoff)->forceDelete();
                app(ImageKeeper::class)->sweep($creatorId);
            });
        }

        return self::SUCCESS;
    }
}
