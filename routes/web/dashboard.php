<?php

use App\Http\Controllers\PulseDashboardController;
use Illuminate\Support\Facades\Route;
use Laravel\Pulse\Http\Middleware\Authorize as AuthorizePulse;

Route::inertia('dashboard', 'Dashboard')->name('dashboard');

Route::get('pulse', PulseDashboardController::class)
    ->middleware([AuthorizePulse::class])
    ->name('pulse');
