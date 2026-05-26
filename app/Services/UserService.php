<?php

namespace App\Services;

use App\DTOs\User\UserData;
use App\Models\User;
use App\Repositories\Users\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class UserService
{
    public function __construct(private readonly UserRepositoryInterface $repository) {}

    public function paginate(Request $request): LengthAwarePaginator
    {
        return $this->repository->paginate($request, ['id', 'name', 'email', 'is_active', 'created_at']);
    }

    public function paginateData(Request $request): LengthAwarePaginator
    {
        /** @var \Illuminate\Pagination\LengthAwarePaginator $paginator */
        $paginator = $this->paginate($request);
        $paginator->getCollection()->loadCount('permissions')->load('roles');

        return $paginator->through(
            fn (User $user) => array_merge(
                UserData::fromModel($user)->toArray(),
                [
                    'roles' => $user->roles->pluck('name')->all(),
                    'permissions_count' => (int) $user->permissions_count,
                ],
            ),
        );
    }

    /**
     * @return array<string, int|string>
     */
    public function toData(User $user): array
    {
        return UserData::fromModel($user)->toArray();
    }

    public function create(array $data): User
    {
        /** @var User $user */
        $user = $this->repository->create($data);

        return $user;
    }

    public function update(User $user, array $data): User
    {
        /** @var User $updated */
        $updated = $this->repository->update($data, $user->id);

        return $updated;
    }

    public function delete(User $user): bool
    {
        return $this->repository->delete($user->id);
    }
}
