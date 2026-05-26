<?php

declare(strict_types=1);

use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('users/table', [UserController::class, 'table'])->name('users.table');
Route::post('users/bulk-assign-roles', [UserController::class, 'bulkAssignRoles'])->name('users.bulk-assign-roles');

Route::get('users/departments', [DepartmentController::class, 'index'])->name('users.departments.index');
Route::match(['get', 'post'], 'users/departments/table', [DepartmentController::class, 'table'])->name('users.departments.table');
Route::post('users/departments', [DepartmentController::class, 'store'])->name('users.departments.store');
Route::put('users/departments/{department}', [DepartmentController::class, 'update'])->name('users.departments.update');
Route::delete('users/departments/{department}', [DepartmentController::class, 'destroy'])->name('users.departments.destroy');
Route::delete('users/departments/bulk-destroy', [DepartmentController::class, 'bulkDestroy'])->name('users.departments.bulk-destroy');

Route::get('users/designations', [DesignationController::class, 'index'])->name('users.designations.index');
Route::match(['get', 'post'], 'users/designations/table', [DesignationController::class, 'table'])->name('users.designations.table');
Route::post('users/designations', [DesignationController::class, 'store'])->name('users.designations.store');
Route::put('users/designations/{designation}', [DesignationController::class, 'update'])->name('users.designations.update');
Route::delete('users/designations/{designation}', [DesignationController::class, 'destroy'])->name('users.designations.destroy');
Route::delete('users/designations/bulk-destroy', [DesignationController::class, 'bulkDestroy'])->name('users.designations.bulk-destroy');
Route::resource('users', UserController::class)->except(['show']);
