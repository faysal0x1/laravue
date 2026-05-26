<?php

namespace App\Providers;

use App\Repositories\ApiSetting\ApiSettingRepository;
use App\Repositories\ApiSetting\ApiSettingRepositoryInterface;
use App\Repositories\AuditLogs\AuditLogRepository;
use App\Repositories\AuditLogs\AuditLogRepositoryInterface;
use App\Repositories\Branch\BranchRepository;
use App\Repositories\Branch\BranchRepositoryInterface;
use App\Repositories\Branch\ManageBranch\ManageBranchRepository;
use App\Repositories\Branch\ManageBranch\ManageBranchRepositoryInterface;
use App\Repositories\Departments\DepartmentRepository;
use App\Repositories\Departments\DepartmentRepositoryInterface;
use App\Repositories\Designations\DesignationRepository;
use App\Repositories\Designations\DesignationRepositoryInterface;
use App\Repositories\Employees\EmployeeRepository;
use App\Repositories\Employees\EmployeeRepositoryInterface;
use App\Repositories\GeneraCharge\GeneraChargeRepository;
use App\Repositories\GeneraCharge\GeneraChargeRepositoryInterface;
use App\Repositories\Merchant\MerchantRepository;
use App\Repositories\Merchant\MerchantRepositoryInterface;
use App\Repositories\PackagingCharge\PackagingChargeRepository;
use App\Repositories\PackagingCharge\PackagingChargeRepositoryInterface;
use App\Repositories\Parcel\ParcelRepository;
use App\Repositories\Parcel\ParcelRepositoryInterface;
use App\Repositories\Rider\RiderRepository;
use App\Repositories\Rider\RiderRepositoryInterface;
use App\Repositories\Roles\RoleRepository;
use App\Repositories\Roles\RoleRepositoryInterface;
use App\Repositories\Settings\GeneralSettingRepository;
use App\Repositories\Settings\GeneralSettingRepositoryInterface;
use App\Repositories\SocialLinks\SocialLinkRepository;
use App\Repositories\SocialLinks\SocialLinkRepositoryInterface;
use App\Repositories\Todos\TodoRepository;
use App\Repositories\Todos\TodoRepositoryInterface;
use App\Repositories\Users\UserRepository;
use App\Repositories\Users\UserRepositoryInterface;
use App\Services\ApiSettingService;
use App\Services\DatabaseBackupService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\Pulse\Pulse;
use Modules\Cache\Contracts\CacheServiceInterface;
use Modules\Cache\Services\CacheService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    #[\Override]
    public function register(): void
    {
        $this->app->singleton(CacheServiceInterface::class, CacheService::class);
        $this->app->singleton(ApiSettingService::class);
        $this->app->singleton(ApiSettingRepositoryInterface::class, ApiSettingRepository::class);
        $this->app->singleton(GeneralSettingRepositoryInterface::class, GeneralSettingRepository::class);

        $this->app->singleton(BranchRepositoryInterface::class, BranchRepository::class);
        $this->app->singleton(DepartmentRepositoryInterface::class, DepartmentRepository::class);
        $this->app->singleton(DesignationRepositoryInterface::class, DesignationRepository::class);

        $this->app->singleton(ManageBranchRepositoryInterface::class, ManageBranchRepository::class);

        $this->app->singleton(EmployeeRepositoryInterface::class, EmployeeRepository::class);
        $this->app->singleton(SocialLinkRepositoryInterface::class, SocialLinkRepository::class);
        $this->app->singleton(TodoRepositoryInterface::class, TodoRepository::class);
        $this->app->singleton(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->singleton(UserRepositoryInterface::class, UserRepository::class);
        $this->app->singleton(MerchantRepositoryInterface::class, MerchantRepository::class);
        $this->app->singleton(RiderRepositoryInterface::class, RiderRepository::class);
        $this->app->singleton(PackagingChargeRepositoryInterface::class, PackagingChargeRepository::class);
        $this->app->singleton(GeneraChargeRepositoryInterface::class, GeneraChargeRepository::class);
        $this->app->singleton(ParcelRepositoryInterface::class, ParcelRepository::class);
        $this->app->singleton(AuditLogRepositoryInterface::class, AuditLogRepository::class);
        $this->app->singleton(DatabaseBackupService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->make(Pulse::class)->ignoreRoutes();

        // Super-admin bypasses all permission checks
        Gate::before(fn ($user, $ability) => $user->hasRole('super-admin') ? true : null);

        Gate::define('viewPulse', function ($user = null) {
            if (! $user) {
                return false;
            }

            if (app()->environment('local')) {
                return true;
            }

            return $user->can('pulse.view');
        });

        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
