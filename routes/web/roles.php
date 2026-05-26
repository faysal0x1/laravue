<?php

declare(strict_types=1);

use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\ValidateRolePrefix;
use Illuminate\Support\Facades\Route;

// Route::middleware(['auth', 'verified', ValidateRolePrefix::class])
Route::middleware('auth', 'verified')
    // ->prefix('{role}')
    ->group(function () {
        Route::get('users/{user}/role', [UserController::class, 'editRole'])->name('users.role');
        Route::put('users/{user}/role', [UserController::class, 'updateRole'])->name('users.role.update');
        Route::get('users/{user}/permissions', [UserController::class, 'editPermissions'])->name('users.permissions');
        Route::put('users/{user}/permissions', [UserController::class, 'updatePermissions'])->name('users.permissions.update');

        // Roles
        Route::match(['get', 'post'], 'roles/table', [RoleController::class, 'table'])->name('roles.table');
        Route::resource('roles', RoleController::class)->except(['show']);
    });
