<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;

class DatabaseBackupCommand extends Command
{
    protected $signature = 'database:backup';

    protected $description = 'Create a database dump in storage (used by nightly schedule).';

    public function handle(DatabaseBackupService $backup): int
    {
        try {
            $file = $backup->create();
            $this->info("Backup saved: {$file}");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            report($e);

            return self::FAILURE;
        }
    }
}
