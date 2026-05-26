<?php

declare(strict_types=1);

namespace App\Repositories\Users;

use App\Models\User;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    protected function getSearchableFields(): array
    {
        return ['name', 'email'];
    }

    protected function getSortableFields(): array
    {
        return ['id', 'name', 'email', 'created_at'];
    }

    public function customers(): Collection
    {
        return User::query()
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }
}
