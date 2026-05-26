<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Permissions are merged from `database/seeders/Data/RolesAndPermissions/{emon,jayead,faysal}.php`.
     *
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = array_values(array_unique(array_merge(
            require __DIR__.'/Data/RolesAndPermissions/liakat.php',
            require __DIR__.'/Data/RolesAndPermissions/faysal.php',
        )));

        sort($permissions);

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        $superAdmin->givePermissionTo(Permission::all());

        $adminExcludedRolePerms = [
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',
        ];

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(
            Permission::query()
                ->whereNotIn('name', $adminExcludedRolePerms)
                ->pluck('name')
                ->all()
        );

        $manager = Role::firstOrCreate(['name' => 'manager']);
        $manager->givePermissionTo([
            'dashboard.view',
            'todo.view',
            'todo.create',
            'todo.edit',
            'todo.delete',
            'todo.bulk-delete',
            'reports.view',
            'reports.export',
            'support-tickets.view',
            'support-tickets.create',
            'support-tickets.update',
            'support-tickets.delete',
            'support-tickets.reply',
            'support-tickets.manage',
            'settings.view',
            'settings.profile',
            'settings.password',
            'settings.appearance',
        ]);

        $employee = Role::firstOrCreate(['name' => 'employee']);
        $employee->givePermissionTo([
            'dashboard.view',
            'todo.view',
            'todo.create',
            'todo.edit',
            'todo.delete',
            'reports.view',
            'support-tickets.view',
            'support-tickets.reply',
            'settings.profile',
            'settings.password',
            'settings.appearance',
        ]);

        $viewer = Role::firstOrCreate(['name' => 'viewer']);
        $viewer->givePermissionTo([
            'dashboard.view',
            'reports.view',
            'support-tickets.view',
        ]);

        $customer = Role::firstOrCreate(['name' => 'customer']);
        $customer->givePermissionTo([
            'dashboard.view',
            'support-tickets.view',
            'support-tickets.create',
            'support-tickets.update',
            'support-tickets.delete',
            'support-tickets.reply',
            'settings.profile',
            'settings.password',
            'settings.appearance',
        ]);

        $this->command->info('✅  Roles and permissions seeded successfully.');
        $this->command->table(
            ['Role', 'Permissions Count'],
            Role::with('permissions')->get()->map(fn ($role) => [
                $role->name,
                $role->permissions->count(),
            ])->toArray()
        );
    }
}
