<?php

use App\Http\Controllers\Settings\DatabaseBackupController;
use App\Http\Controllers\Settings\GeneralSettingController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SecurityController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('settings/general', [GeneralSettingController::class, 'edit'])
        ->middleware('can:general-setting.edit')
        ->name('general-settings.edit');
    Route::put('settings/general', [GeneralSettingController::class, 'update'])
        ->middleware('can:general-setting.edit')
        ->name('general-settings.update');

    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/security', [SecurityController::class, 'edit'])->name('security.edit');

    Route::put('settings/password', [SecurityController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('user-password.update');

    Route::inertia('settings/appearance', 'settings/Appearance')->name('appearance.edit');

    Route::get('settings/database-backup', [DatabaseBackupController::class, 'edit'])->name('database-backup.edit');
    Route::post('settings/database-backup', [DatabaseBackupController::class, 'store'])
        ->middleware('throttle:12,60')
        ->name('database-backup.store');
    Route::get('settings/database-backup/download/{filename}', [DatabaseBackupController::class, 'download'])
        ->where('filename', 'backup-(mysql|mariadb|pgsql|sqlite)-\d{4}-\d{2}-\d{2}-\d{6}\.(sql|sqlite)')
        ->name('database-backup.download');
});
