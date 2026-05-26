<?php

namespace App\Services;

use App\Models\NavModule;
use App\Models\NavSubModule;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

class SidebarNavigationService
{
    public function forgetNavigationCache(): void
    {
        Cache::forget($this->cacheKey());
    }

    /**
     * Cached tree for Inertia (JSON-serializable: no callables).
     *
     * @return array<int, array<string, mixed>>
     */
    public function getNavigationForInertia(): array
    {
        $ttl = max(60, (int) config('sidebar.cache_ttl', 3600));

        return Cache::remember($this->cacheKey(), $ttl, fn () => $this->buildNavigation());
    }

    public function cacheKey(): string
    {
        return (string) config('sidebar.cache_key', 'sidebar.navigation');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildNavigation(): array
    {
        return NavModule::query()
            ->where('is_active', true)
            ->with([
                'subModules' => fn ($q) => $q
                    ->where('is_active', true)
                    ->orderBy('sort_order'),
            ])
            ->orderBy('sort_order')
            ->get()
            ->map(fn (NavModule $module) => $this->mapModule($module))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function mapModule(NavModule $module): ?array
    {
        $children = $module->subModules
            ->map(fn (NavSubModule $sub) => $this->mapSubModule($sub))
            ->filter()
            ->values()
            ->all();

        $href = $this->routeUrl($module->route_name);

        $item = [
            'title' => $module->title,
            'route' => $module->route_name,
            'icon' => $module->icon,
            'permission' => $module->permission,
            'href' => $href,
        ];

        if ($children !== []) {
            $item['children'] = $children;
        }

        if ($href === null && ($children === [])) {
            return null;
        }

        return $item;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function mapSubModule(NavSubModule $sub): ?array
    {
        $href = $this->routeUrl($sub->route_name);
        if ($href === null) {
            return null;
        }

        return [
            'title' => $sub->title,
            'route' => $sub->route_name,
            'icon' => $sub->icon,
            'permission' => $sub->permission,
            'href' => $href,
        ];
    }

    private function routeUrl(?string $routeName): ?string
    {
        if ($routeName === null || $routeName === '') {
            return null;
        }

        if (! Route::has($routeName)) {
            return null;
        }

        try {
            return route($routeName);
        } catch (\Throwable) {
            return null;
        }
    }
}
