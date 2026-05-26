<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\SidebarNavigationService;
use Illuminate\Support\ServiceProvider;

class SidebarNavigationServiceProvider extends ServiceProvider
{
    #[\Override]
    public function register(): void
    {
        $this->app->singleton(SidebarNavigationService::class);
    }

    public function boot(): void
    {
        //
    }
}
