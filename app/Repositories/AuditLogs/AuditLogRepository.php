<?php

namespace App\Repositories\AuditLogs;

use App\Helpers\QueryBuilderHelper;
use App\Models\AuditLog;
use App\Repositories\BaseRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AuditLogRepository extends BaseRepository implements AuditLogRepositoryInterface
{
    public function __construct(AuditLog $model)
    {
        parent::__construct($model);
    }

    #[\Override]
    public function paginate(Request $request, array $columns = ['*']): LengthAwarePaginator
    {
        $query = $this->model->query()->select($columns);
        $params = $request->isMethod('post') ? $request->all() : $request->query();
        $combinedRequest = new Request($params);

        if ($action = $combinedRequest->input('action')) {
            $query->where('action', $action);
        }

        if ($modelType = $combinedRequest->input('model_type')) {
            $query->where('model_type', $modelType);
        }

        $query = QueryBuilderHelper::apply(
            $combinedRequest,
            $query,
            $this->getSearchableFields(),
            $this->getSortableFields(),
        );

        return QueryBuilderHelper::paginate($combinedRequest, $query);
    }

    protected function getSearchableFields(): array
    {
        return ['action', 'model_type', 'ip_address', 'user_agent'];
    }

    protected function getSortableFields(): array
    {
        return ['id', 'user_id', 'action', 'model_type', 'created_at'];
    }
}
