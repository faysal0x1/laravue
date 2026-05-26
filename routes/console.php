<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('database:backup')->dailyAt(config('database_backup.schedule_backup_at', '02:00'));

Schedule::command('database:backup-prune')->dailyAt(config('database_backup.schedule_prune_at', '03:00'));
