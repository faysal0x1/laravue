<?php

namespace App\Models;

use App\Services\SidebarNavigationService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Top-level sidebar module (parent row in `modules`).
 *
 * @property-read Collection<int, NavSubModule> $subModules
 */
class NavModule extends Model
{
    protected $table = 'modules';

    /** @var list<string> */
    protected $fillable = [
        'id',
        'title',
        'route_name',
        'icon',
        'permission',
        'sort_order',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    #[\Override]
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    #[\Override]
    protected static function booted(): void
    {
        static::saved(function () {
            if (app()->bound(SidebarNavigationService::class)) {
                resolve(SidebarNavigationService::class)->forgetNavigationCache();
            }
        });
        static::deleted(function () {
            if (app()->bound(SidebarNavigationService::class)) {
                resolve(SidebarNavigationService::class)->forgetNavigationCache();
            }
        });
    }

    /** @return HasMany<NavSubModule, $this> */
    public function subModules(): HasMany
    {
        return $this->hasMany(NavSubModule::class, 'module_id');
    }
}
