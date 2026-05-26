<?php

namespace App\Services;

use App\DTOs\AuditLog\AuditLogData;
use App\Models\AuditLog;
use App\Repositories\AuditLogs\AuditLogRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class AuditLogService
{
    public function __construct(private readonly AuditLogRepositoryInterface $repository) {}

    public function paginate(Request $request): LengthAwarePaginator
    {
        return $this->repository->paginate($request, ['id', 'user_id', 'action', 'model_type', 'model_id', 'old_values', 'new_values', 'ip_address', 'user_agent', 'created_at']);
    }

    public function paginateData(Request $request): LengthAwarePaginator
    {
        return $this->paginate($request)->through(
            fn (AuditLog $log) => AuditLogData::fromModel($log)->toArray(),
        );
    }
}
