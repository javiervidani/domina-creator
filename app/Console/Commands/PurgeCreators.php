<?php

namespace App\Console\Commands;

use App\Tenancy\CreatorDeletion;
use Illuminate\Console\Command;

class PurgeCreators extends Command
{
    protected $signature = 'creators:purge';

    protected $description = 'Drop the schema and role of every creator whose 60 days of recovery have passed';

    public function handle(CreatorDeletion $deletion): int
    {
        $this->info($deletion->purgeDue().' creator(s) purged.');

        return self::SUCCESS;
    }
}
