<?php

declare(strict_types=1);

namespace App\Repositories\Employees;

use App\Models\Employee;
use App\Repositories\BaseRepository;

class EmployeeRepository extends BaseRepository implements EmployeeRepositoryInterface
{
    public function __construct(Employee $model)
    {
        parent::__construct($model);
    }

    protected function getSearchableFields(): array
    {
        return ['name', 'email', 'position'];
    }

    protected function getSortableFields(): array
    {
        return ['id', 'name', 'email', 'position', 'salary', 'created_at'];
    }
}
