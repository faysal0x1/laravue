<?php

use App\Http\Controllers\MediaFileController;
use App\Http\Controllers\SocialLinkController;
use App\Http\Controllers\TodoController;
use App\Http\Middleware\ValidateRolePrefix;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

/*
 * Spatie media URLs (/media/{id}/...) — serve from storage/app/media when public symlink is unavailable.
 */
Route::get('media/{path}', MediaFileController::class)
    ->where('path', '.*')
    ->name('media.files');

// Route::middleware(['auth', 'verified', ValidateRolePrefix::class])
Route::middleware('auth')
    // ->prefix('{role}')
    ->group(function () {
        foreach (glob(__DIR__.'/web/*.php') as $filename) {
            require $filename;
        }

        // Social Link routes
        Route::get('social-links', [SocialLinkController::class, 'index'])->name('social-link.index');
        Route::match(['get', 'post'], 'social-links/table', [SocialLinkController::class, 'table'])
            ->name('social-link.table');
        Route::post('social-links', [SocialLinkController::class, 'store'])->name('social-link.store');
        Route::delete('social-links/bulk-destroy', [SocialLinkController::class, 'bulkDestroy'])->name('social-link.bulk-destroy');
        Route::put('social-links/{socialLink}', [SocialLinkController::class, 'update'])->name('social-link.update');
        Route::put('social-links/{socialLink}/toggle-status', [SocialLinkController::class, 'toggleStatus'])->name('social-link.toggle-status');
        Route::delete('social-links/{socialLink}', [SocialLinkController::class, 'destroy'])->name('social-link.destroy');

        // Todo routes
        Route::get('todos', [TodoController::class, 'index'])->name('todo.index');
        Route::post('todos', [TodoController::class, 'store'])->name('todo.store');
        Route::delete('todos/bulk-destroy', [TodoController::class, 'bulkDestroy'])->name('todo.bulk-destroy');
        Route::put('todos/{todo}', [TodoController::class, 'update'])->name('todo.update');
        Route::put('todos/{todo}/toggle', [TodoController::class, 'toggle'])->name('todo.toggle');
        Route::delete('todos/{todo}', [TodoController::class, 'destroy'])->name('todo.destroy');
    });

require __DIR__.'/settings.php';
