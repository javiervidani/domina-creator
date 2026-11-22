<?php

namespace App\Console\Commands;

use App\Backup\CreatorBackup;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class BackupCreators extends Command
{
    protected $signature = 'creators:backup {--keep=7 : how many backups to keep per creator}';

    protected $description = 'Encrypted pg_dump of every active creator schema, newest N kept';

    public function handle(CreatorBackup $backup): int
    {
        $failed = 0;
        foreach (Tenant::where('status', 'active')->pluck('creator_id') as $creatorId) {
            try {
                $file = $backup->backup($creatorId);
                $backup->prune($creatorId, (int) $this->option('keep'));
                $this->line("creator {$creatorId}: {$file}");
            } catch (Throwable $e) {
                $failed++;
                Log::error("Backup of creator {$creatorId} failed: ".$e->getMessage()); // one failure must not stop the others
                $this->error("creator {$creatorId}: ".$e->getMessage());
            }
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
