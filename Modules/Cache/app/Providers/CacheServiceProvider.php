<?php

namespace Modules\Cache\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Modules\Cache\Contracts\CacheServiceInterface;
use Modules\Cache\Services\CacheService;
use Nwidart\Modules\Support\ModuleServiceProvider;

class CacheServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Cache';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'cache';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    // protected array $commands = [];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    /**
     * Define module schedules.
     *
     * @param  Schedule  $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }

    public function register(): void
    {
        $this->app->singleton(CacheServiceInterface::class, CacheService::class);

        parent::register();
    }
}
