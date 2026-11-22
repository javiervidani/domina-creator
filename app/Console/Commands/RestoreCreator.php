<?php

namespace App\Console\Commands;

use App\Backup\CreatorBackup;
use Illuminate\Console\Command;
use Throwable;

class RestoreCreator extends Command
{
    protected $signature = 'creators:restore {creator : creator id} {file : a backup file, for example storage/app/private/backups/7/2026-11-22_030000.dump.enc}';

    protected $description = 'Restore one creator from a backup into an empty schema (drop the damaged schema first)';

    public function handle(CreatorBackup $backup): int
    {
        try {
            $backup->restore((int) $this->argument('creator'), (string) $this->argument('file'));
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
        $this->info('Restored. Run creators:migrate to bring the schema up to date if the code is newer than the backup.');

        return self::SUCCESS;
    }
}
