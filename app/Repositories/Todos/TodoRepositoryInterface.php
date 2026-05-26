<?php

declare(strict_types=1);

namespace App\Repositories\Todos;

use App\Repositories\Interfaces\RepositoryInterface;

interface TodoRepositoryInterface extends RepositoryInterface
{
    /**
     * @param  array<int, int>  $ids
     */
    public function bulkDelete(array $ids): int;
}
