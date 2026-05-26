<?php

declare(strict_types=1);

return [

    'directory' => env('DATABASE_BACKUP_DIRECTORY', 'database-backups'),

    'retention_days' => (int) env('DATABASE_BACKUP_RETENTION_DAYS', 7),

    'schedule_backup_at' => env('DATABASE_BACKUP_SCHEDULE_AT', '02:00'),

    'schedule_prune_at' => env('DATABASE_BACKUP_PRUNE_SCHEDULE_AT', '03:00'),

    'mysqldump_path' => env('DATABASE_BACKUP_MYSQLDUMP_PATH', 'mysqldump'),

    'pg_dump_path' => env('DATABASE_BACKUP_PG_DUMP_PATH', 'pg_dump'),

];
