<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Tenancy\CreatorMigrator;
use Illuminate\Console\Command;

class MigrateCreators extends Command
{
    protected $signature = 'creators:migrate';

    protected $description = 'Run new creator migrations in every active creator schema';

    public function handle(CreatorMigrator $migrator): int
    {
        foreach (Tenant::where('status', 'active')->pluck('creator_id') as $id) {
            $this->line("creator_{$id}");
            $this->output->write($migrator->migrate($id));
        }

        return self::SUCCESS;
    }
}
