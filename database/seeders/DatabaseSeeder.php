<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Shop;
use Illuminate\Database\Seeder;
use Modules\LocationApi\Database\Seeders\ApiSettingsSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        // Seed roles and permissions first
        $this->call(RolesAndPermissionsSeeder::class);
        $this->call(ModuleSeeder::class);
        $this->call(SubModuleSeeder::class);

        $this->call(UsersSeeder::class);
    }
}
