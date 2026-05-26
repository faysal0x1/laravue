<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Services\DatabaseBackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class DatabaseBackupController extends Controller
{
    public function __construct(private readonly DatabaseBackupService $backup) {}

    public function edit(Request $request): Response
    {
        return Inertia::render('settings/DatabaseBackup', [
            'backups' => $this->backup->listBackups(),
            'retentionDays' => (int) config('database_backup.retention_days', 7),
            'scheduleBackupAt' => (string) config('database_backup.schedule_backup_at'),
            'schedulePruneAt' => (string) config('database_backup.schedule_prune_at'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            $filename = $this->backup->create();
            Inertia::flash('toast', [
                'type' => 'success',
                'message' => __('Backup created: :file', ['file' => $filename]),
            ]);
        } catch (Throwable $e) {
            report($e);

            Inertia::flash('toast', [
                'type' => 'error',
                'message' => $e->getMessage(),
            ]);
        }

        return to_route('database-backup.edit');
    }

    public function download(string $filename): BinaryFileResponse
    {
        $path = $this->backup->absolutePathForDownload($filename);

        return response()->download($path, $filename)->deleteFileAfterSend(false);
    }
}
