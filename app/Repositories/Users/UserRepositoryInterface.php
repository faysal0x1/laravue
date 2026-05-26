<?php

declare(strict_types=1);

namespace App\Repositories\Users;

use App\Repositories\Interfaces\RepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface extends RepositoryInterface
{
    public function customers(): Collection;
}
