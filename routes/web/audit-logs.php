<?php

declare(strict_types=1);

use App\Http\Controllers\AuditLogController;
use Illuminate\Support\Facades\Route;

Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
Route::post('audit-logs/table', [AuditLogController::class, 'table'])->name('audit-logs.table');
