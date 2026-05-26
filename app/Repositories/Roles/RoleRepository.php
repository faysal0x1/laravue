<?php

namespace App\Repositories\Roles;

use App\Helpers\QueryBuilderHelper;
use App\Repositories\BaseRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Role;

class RoleRepository extends BaseRepository implements RoleRepositoryInterface
{
    public function __construct(Role $model)
    {
        parent::__construct($model);
    }

    protected function getSearchableFields(): array
    {
        return ['name'];
    }

    protected function getSortableFields(): array
    {
        return ['id', 'name', 'created_at', 'status'];
    }

    public function paginateWithPermissions(Request $request): LengthAwarePaginator
    {
        $query = $this->model->query()
            ->select(['id', 'name', 'guard_name', 'status', 'created_at'])
            ->withCount('permissions');

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
}
