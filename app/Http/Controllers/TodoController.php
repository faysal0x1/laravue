<?php

namespace App\Http\Controllers;

use App\Helpers\QueryBuilderHelper;
use App\Http\Requests\Todos\BulkDestroyTodoRequest;
use App\Http\Requests\Todos\StoreTodoRequest;
use App\Http\Requests\Todos\UpdateTodoRequest;
use App\Models\Todo;
use App\Models\User;
use App\Services\TodoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TodoController extends Controller
{
    public function __construct(private readonly TodoService $service) {}

    /**
     * @return array<int, string>
     */
    private function userNames(): array
    {
        return User::query()
            ->orderBy('name')
            ->pluck('name')
            ->values()
            ->all();
    }

    private function renderIndex(Request $request): Response
    {
        return Inertia::render('Todos/Index', [
            'todos' => $this->service->paginateData($request),
            'users' => fn () => $this->userNames(),
            'filters' => array_merge(
                QueryBuilderHelper::filters($request),
                $request->only(['filter']),
            ),
            'stats' => fn () => $this->service->stats(),
        ]);
    }

    public function index(Request $request): Response
    {
        return $this->renderIndex($request);
    }

    public function store(StoreTodoRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return success_route('todo.index', 'Task created successfully.');
    }

    public function update(UpdateTodoRequest $request, Todo $todo): RedirectResponse
    {
        $this->service->update($todo, $request->validated());

        return success_route('todo.index', 'Task updated successfully.');
    }

    public function toggle(Todo $todo): RedirectResponse
    {
        $this->service->toggle($todo);

        return success_route('todo.index', $todo->done ? 'Task marked as active.' : 'Task marked as done.');
    }

    public function destroy(Todo $todo): RedirectResponse
    {
        $this->service->delete($todo);

        return success_route('todo.index', 'Task deleted successfully.');
    }

    public function bulkDestroy(BulkDestroyTodoRequest $request): RedirectResponse
    {
        $this->service->bulkDelete($request->ids());

        return success_route('todo.index', 'Selected tasks deleted successfully.');
    }
}
