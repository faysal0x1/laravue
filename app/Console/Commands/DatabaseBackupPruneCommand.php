<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;

class DatabaseBackupPruneCommand extends Command
{
    protected $signature = 'database:backup-prune';

    protected $description = 'Remove database backup files older than the configured retention window.';

    public function handle(DatabaseBackupService $backup): int
    {
        try {
            $days = (int) config('database_backup.retention_days', 7);
            $removed = $backup->pruneOlderThan($days);
            $this->info("Pruned backups older than {$days} days: {$removed} file(s).");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            report($e);

            return self::FAILURE;
        }
    }
}
