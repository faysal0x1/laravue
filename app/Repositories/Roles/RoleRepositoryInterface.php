<?php

namespace App\Repositories\Roles;

use App\Repositories\Interfaces\RepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

interface RoleRepositoryInterface extends RepositoryInterface
{
    public function paginateWithPermissions(Request $request): LengthAwarePaginator;
}
