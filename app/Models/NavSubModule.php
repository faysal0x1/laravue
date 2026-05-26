<?php

namespace App\Models;

use App\Services\SidebarNavigationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Child sidebar item (row in `sub_modules`).
 */
class NavSubModule extends Model
{
    protected $table = 'sub_modules';

    /** @var list<string> */
    protected $fillable = [
        'id',
        'module_id',
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

    /** @return BelongsTo<NavModule, $this> */
    public function module(): BelongsTo
    {
        return $this->belongsTo(NavModule::class, 'module_id');
    }
}
