<?php

namespace App\Http\Controllers;

use App\Helpers\QueryBuilderHelper;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(private readonly UserService $service) {}

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
        $users = $this->service->paginateData($request);

        $availableRoles = Role::orderBy('name')
            ->withCount('permissions')
            ->get()
            ->map(fn (Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions_count' => (int) $role->permissions_count,
            ])
            ->values()
            ->all();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => QueryBuilderHelper::filters($request),
            'availableRoles' => $availableRoles,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Users/Create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return success_route('users.index', 'User created successfully.');
    }

    public function edit(User $user): Response
    {
        return Inertia::render('Users/Edit', [
            'user' => $this->service->toData($user),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->service->update($user, $request->validated());

        return success_route('users.index', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->service->delete($user);

        return success_route('users.index', 'User deleted successfully.');
    }

    public function bulkAssignRoles(Request $request): RedirectResponse
    {
        $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', Rule::exists('users', 'id')],
            'roles' => ['array'],
            'roles.*' => ['string', Rule::exists('roles', 'name')],
        ]);

        User::whereIn('id', $request->input('user_ids'))
            ->get()
            ->each(fn (User $user) => $user->syncRoles($request->input('roles', [])));

        return success_route('users.index', 'Roles assigned to '.count($request->input('user_ids')).' user(s) successfully.');
    }

    public function editRole(User $user): Response
    {
        $availableRoles = Role::orderBy('name')
            ->withCount('permissions')
            ->get()
            ->map(fn (Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions_count' => (int) $role->permissions_count,
            ])
            ->values()
            ->all();

        return Inertia::render('Users/AssignRole', [
            'user' => ['id' => $user->id, 'name' => $user->name],
            'availableRoles' => $availableRoles,
            'userRoles' => $user->roles->pluck('name')->values()->all(),
        ]);
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'roles' => ['array'],
            'roles.*' => ['string', Rule::exists('roles', 'name')],
        ]);

        $user->syncRoles($request->input('roles', []));

        return success_route('users.index', 'Roles updated successfully.');
    }

    public function editPermissions(User $user): Response
    {
        $user->load('permissions');

        return Inertia::render('Users/AssignPermission', [
            'user' => ['id' => $user->id, 'name' => $user->name],
            'permissionModules' => $this->permissionModules(),
            'userPermissions' => $user->permissions->pluck('name')->values()->all(),
        ]);
    }

    public function updatePermissions(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'permissions' => ['array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
        ]);

        $user->syncPermissions($request->input('permissions', []));

        return success_route('users.index', 'Permissions updated successfully.');
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
                $modulePermissions = $items->map(function (Permission $permission) use ($module) {
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
                    'permissions' => $modulePermissions,
                ];
            })
            ->values()
            ->all();
    }
}
