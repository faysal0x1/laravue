<?php

namespace App\Repositories;

use App\Helpers\QueryBuilderHelper;
use App\Repositories\Interfaces\RepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

abstract class BaseRepository implements RepositoryInterface
{
    public function __construct(protected Model $model) {}

    public function all(array $columns = ['*']): array
    {
        return $this->model->all($columns)->toArray();
    }

    public function paginate(Request $request, array $columns = ['*']): LengthAwarePaginator
    {
        $query = $this->model->query()->select($columns);
        $params = $request->isMethod('post') ? $request->all() : $request->query();
        $combinedRequest = new Request($params);

        $query = QueryBuilderHelper::apply(
            $combinedRequest,
            $query,
            $this->getSearchableFields(),
            $this->getSortableFields(),
        );

        return QueryBuilderHelper::paginate($combinedRequest, $query);
    }

    public function create(array $data): Model
    {
        return DB::transaction(fn () => $this->model->create($data));
    }

    public function update(array $data, int $id): Model
    {
        return DB::transaction(function () use ($data, $id) {
            $model = $this->find($id);
            if (! $model instanceof Model) {
                abort(404);
            }

            $model->update($data);

            return $model->fresh() ?? $model;
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $model = $this->find($id);
            if (! $model instanceof Model) {
                return false;
            }

            return (bool) $model->delete();
        });
    }

    public function find(int $id, array $columns = ['*']): ?Model
    {
        return $this->model->find($id, $columns);
    }

    public function findBy(string $field, mixed $value, array $columns = ['*']): ?Model
    {
        return $this->model->where($field, $value)->first($columns);
    }

    public function query(): Builder
    {
        return $this->model->query();
    }

    abstract protected function getSearchableFields(): array;

    abstract protected function getSortableFields(): array;

    public function getWithRelations($id = null, array $func = ['*']): Model|Collection|null
    {
        if ($id === null) {
            return $this->model->with($func)->get();
        }

        return $this->model->with($func)->find($id);
    }
}
