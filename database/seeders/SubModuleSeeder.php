<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\NavSubModule;
use App\Services\SidebarNavigationService;
use Illuminate\Database\Seeder;

class SubModuleSeeder extends Seeder
{
    public const MODULE_ID_SETTINGS = ModuleSeeder::ID_SETTINGS;

    public const ID_PROFILE = 1;

    public const ID_SECURITY = 2;

    public const ID_APPEARANCE = 3;

    public const ID_DATABASE_BACKUP = 4;

    /**
     * Sub-module rows are merged from `database/seeders/Data/SubModuleSeeder/{emon,jayead,faysal}.php`.
     *
     * Child items under grouped modules (`sub_modules` table).
     *
     * `module_id` references {@see ModuleSeeder::ID_SETTINGS} (Settings group).
     */
    public function run(): void
    {
        $rows = array_merge(
            require __DIR__.'/Data/SubModuleSeeder/liakat.php',
            require __DIR__.'/Data/SubModuleSeeder/faysal.php',
        );

        usort($rows, fn (array $a, array $b): int => $a['sort_order'] <=> $b['sort_order']);

        foreach ($rows as $row) {
            NavSubModule::query()->updateOrCreate(
                ['id' => $row['id']],
                [...$row, 'is_active' => true],
            );
        }

        if (app()->bound(SidebarNavigationService::class)) {
            resolve(SidebarNavigationService::class)->forgetNavigationCache();
        }
    }
}
