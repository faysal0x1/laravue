<?php

namespace App\Services;

use App\Repositories\Roles\RoleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class RoleService
{
    public function __construct(private RoleRepositoryInterface $repository) {}

    public function paginateData(Request $request): LengthAwarePaginator
    {
        return $this->repository->paginateWithPermissions($request)->through(
            fn (Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
                'slug' => Str::slug($role->name),
                'permissions_count' => (int) ($role->permissions_count ?? 0),
                'status' => $role->status ?? 'active',
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toData(Role $role): array
    {
        $role->load('permissions');

        return [
            'id' => $role->id,
            'name' => $role->name,
            'status' => $role->status ?? 'active',
            'permissions' => $role->permissions->pluck('name')->values()->all(),
        ];
    }

    public function create(array $data): Role
    {
        /** @var Role $role */
        $role = $this->repository->create([
            'name' => $data['name'],
            'guard_name' => $this->guardName(),
        ]);

        $this->syncRoleDetails($role, $data);

        return $role->fresh() ?? $role;
    }

    public function update(Role $role, array $data): Role
    {
        $role->name = $data['name'];
        $this->syncRoleDetails($role, $data);

        return $role->fresh() ?? $role;
    }

    public function delete(Role $role): bool
    {
        return (bool) $role->delete();
    }

    private function syncRoleDetails(Role $role, array $data): void
    {
        $role->guard_name = $this->guardName();
        $role->status = $data['status'] ?? $role->status ?? 'active';
        $role->save();

        $role->syncPermissions($data['permissions'] ?? []);
    }

    private function guardName(): string
    {
        return (string) config('auth.defaults.guard', 'web');
    }
}
