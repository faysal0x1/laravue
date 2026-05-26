<?php

namespace App\Http\Controllers;

use App\Helpers\QueryBuilderHelper;
use App\Http\Requests\Roles\StoreRoleRequest;
use App\Http\Requests\Roles\UpdateRoleRequest;
use App\Services\RoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(private RoleService $service) {}

    public function index(Request $request): Response
    {
        return $this->renderIndex($request);
    }

    public function table(Request $request): Response
    {
        return $this->renderIndex($request);
    }

    private function renderIndex(Request $request): Response
    {
        $roles = $this->service->paginateData($request);

        return Inertia::render('Roles/Index', [
            'roles' => $roles,
            'filters' => QueryBuilderHelper::filters($request),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Roles/Create', [
            'permissionModules' => $this->permissionModules(),
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return success_route('roles.index', 'Role created successfully.');
    }

    public function edit(Role $role): Response
    {
        return Inertia::render('Roles/Edit', [
            'role' => $this->service->toData($role),
            'permissionModules' => $this->permissionModules(),
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $this->service->update($role, $request->validated());

        return success_route('roles.index', 'Role updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->service->delete($role);

        return success_route('roles.index', 'Role deleted successfully.');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function permissionModules(): array
    {
        $permissions = Permission::query()
            ->select(['name'])
            ->orderBy('name')
            ->get();

        return $permissions
            ->groupBy(function (Permission $permission) {
                $module = Str::of($permission->name ?? '')->before('.')->value();

                return $module !== '' ? $module : 'general';
            })
            ->map(function ($items, $module) {
                $label = Str::of($module)->replace(['_', '-'], ' ')->title()->value();
                $permissions = $items->map(function (Permission $permission) use ($module) {
                    $name = $permission->name;
                    $rawLabel = $module === 'general'
                        ? Str::of($name)
                        : Str::of($name)->after($module.'.');

                    return [
                        'name' => $name,
                        'label' => $rawLabel->replace(['_', '-', '.'], ' ')->title()->value(),
                    ];
                })->values()->all();

                return [
                    'key' => $module,
                    'label' => $label,
                    'permissions' => $permissions,
                ];
            })
            ->values()
            ->all();
    }
}
