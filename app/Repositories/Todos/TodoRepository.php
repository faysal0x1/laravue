<?php

declare(strict_types=1);

namespace App\Repositories\Todos;

use App\Models\Todo;
use App\Repositories\BaseRepository;

class TodoRepository extends BaseRepository implements TodoRepositoryInterface
{
    public function __construct(Todo $model)
    {
        parent::__construct($model);
    }

    protected function getSearchableFields(): array
    {
        return ['title', 'description', 'assignee'];
    }

    protected function getSortableFields(): array
    {
        return ['id', 'title', 'assignee', 'due_date', 'done', 'created_at'];
    }

    /**
     * @param  array<int, int>  $ids
     */
    public function bulkDelete(array $ids): int
    {
        return $this->model->newQuery()->whereIn('id', $ids)->delete();
    }
}
