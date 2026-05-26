<?php

namespace Database\Seeders;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UsersSeeder extends Seeder
{
 public function run(): void
    {
        $shop = Shop::first();

        $demoUsers = [
            [
                'name' => 'Admin User',
                'email' => 'admin@gmail.com',
                'type' => 'admin',
                'role' => 'admin',
                'shop_id' => $shop->id,
            ],
            [
                'name' => 'Manager User',
                'email' => 'manager@gmail.com',
                'type' => 'manager',
                'role' => 'manager',
                'shop_id' => $shop->id,
            ],
            [
                'name' => 'Staff User',
                'email' => 'staff@gmail.com',
                'type' => 'staff',
                'role' => 'employee',
                'shop_id' => $shop->id,
            ],
            [
                'name' => 'Customer User',
                'email' => 'customer@gmail.com',
                'type' => 'customer',
                'role' => 'customer',
                'shop_id' => $shop->id,
            ],
        ];

        foreach ($demoUsers as $demoUser) {
            $role = $demoUser['role'];
            unset($demoUser['role']);

            $user = User::query()->firstOrNew([
                'email' => $demoUser['email'],
            ]);

            $user->forceFill([
                ...$demoUser,
                'phone' => $user->phone ?: fake()->unique()->numerify('01#########'),
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_active' => true,
            ])->save();

            if (Role::query()->where('name', $role)->exists()) {
                $user->syncRoles([$role]);
            }
        }

        User::factory()
            ->count(25)
            ->sequence(
                ['type' => 'customer'],
                ['type' => 'merchant'],
                ['type' => 'rider'],
                ['type' => 'staff'],
            )
            ->create([
                'shop_id' => $shop->id,
                'phone' => fn () => fake()->unique()->numerify('01#########'),
                'is_active' => fn () => fake()->boolean(90),
            ])
            ->each(function (User $user): void {
                $role = match ($user->type) {
                    'staff' => 'employee',
                    'customer' => 'customer',
                    default => 'viewer',
                };

                if (Role::query()->where('name', $role)->exists()) {
                    $user->assignRole($role);
                }
            });
    }
}
