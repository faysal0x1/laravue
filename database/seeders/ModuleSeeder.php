<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\NavModule;
use App\Services\SidebarNavigationService;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    public const ID_DASHBOARD = 1;

    public const ID_USERS = 2;

    public const ID_EMPLOYEES = 3;

    public const ID_REPORTS = 4;

    public const ID_SUPPORT = 5;

    public const ID_SETTINGS = 6;

    public const ID_AUDIT_LOG = 7;

    public const ID_PULSE = 8;

    /**
     * Module rows are merged from `database/seeders/Data/ModuleSeeder/{emon,jayead,faysal}.php`
     * so each developer edits one file without conflicting merges.
     *
     * Top-level sidebar modules (`modules` table).
     */
    public function run(): void
    {
        $rows = array_merge(
            require __DIR__.'/Data/ModuleSeeder/liakat.php',
            require __DIR__.'/Data/ModuleSeeder/faysal.php',
        );

        usort($rows, fn (array $a, array $b): int => $a['sort_order'] <=> $b['sort_order']);

        foreach ($rows as $row) {
            NavModule::query()->updateOrCreate(
                ['id' => $row['id']],
                [...$row, 'is_active' => true],
            );
        }

        if (app()->bound(SidebarNavigationService::class)) {
            resolve(SidebarNavigationService::class)->forgetNavigationCache();
        }
    }
}
