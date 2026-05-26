<?php

namespace App\Services;

use App\DTOs\Todo\TodoData;
use App\Models\Todo;
use App\Repositories\Todos\TodoRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class TodoService
{
    public function __construct(
        private readonly TodoRepositoryInterface $repository,
    ) {}

    public function paginate(Request $request): LengthAwarePaginator
    {
        return $this->repository->paginate(
            $request,
            ['id', 'title', 'description', 'assignee', 'due_date', 'done', 'created_at'],
        );
    }

    public function paginateData(Request $request): LengthAwarePaginator
    {
        /** @var \Illuminate\Pagination\LengthAwarePaginator $paginator */
        $paginator = $this->paginate($request);

        return $paginator->through(
            fn (Todo $todo) => TodoData::fromModel($todo)->toArray(),
        );
    }

    /**
     * @return array<string, int>
     */
    public function stats(): array
    {
        $total = $this->repository->query()->count();
        $done = $this->repository->query()->where('done', true)->count();

        return [
            'total' => $total,
            'done' => $done,
            'active' => $total - $done,
        ];
    }

    public function create(array $data): Todo
    {
        $todo = $this->repository->create($data);

        audit_log(
            action: 'todos.created',
            modelType: Todo::class,
            modelId: $todo->id,
            newValues: $todo->only(['title', 'assignee', 'due_date', 'done']),
        );

        return $todo;
    }

    public function update(Todo $todo, array $data): Todo
    {
        $before = $todo->only(['title', 'description', 'assignee', 'due_date', 'done']);

        /** @var Todo $updated */
        $updated = $this->repository->update($data, $todo->id);

        audit_log(
            action: 'todos.updated',
            modelType: Todo::class,
            modelId: $updated->id,
            oldValues: $before,
            newValues: $updated->only(['title', 'description', 'assignee', 'due_date', 'done']),
        );

        return $updated;
    }

    public function toggle(Todo $todo): Todo
    {
        return $this->update($todo, ['done' => ! $todo->done]);
    }

    public function delete(Todo $todo): bool
    {
        $before = $todo->only(['title', 'assignee', 'due_date', 'done']);
        $deleted = $this->repository->delete($todo->id);

        if ($deleted) {
            audit_log(
                action: 'todos.deleted',
                modelType: Todo::class,
                modelId: $todo->id,
                oldValues: $before,
            );
        }

        return $deleted;
    }

    /**
     * @param  array<int, int>  $ids
     */
    public function bulkDelete(array $ids): int
    {
        $todos = Todo::query()
            ->whereIn('id', $ids)
            ->get(['id', 'title', 'assignee']);

        $deleted = $this->repository->bulkDelete($ids);

        if ($deleted > 0) {
            audit_log(
                action: 'todos.bulk-deleted',
                modelType: Todo::class,
                oldValues: ['todos' => $todos->toArray()],
                newValues: ['deleted_count' => $deleted, 'ids' => $ids],
            );
        }

        return $deleted;
    }
}
