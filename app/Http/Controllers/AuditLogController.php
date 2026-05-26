<?php

namespace App\Http\Controllers;

use App\Helpers\QueryBuilderHelper;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    public function __construct(private readonly AuditLogService $service) {}

    public function index(Request $request): Response
    {
        return $this->renderIndex($request);
    }

    public function table(Request $request): Response
    {
        return $this->renderIndex($request);
    }

    private function renderIndex(Request $request): Response
    {
        $logs = $this->service->paginateData($request);

        return Inertia::render('AuditLogs/Index', [
            'auditLogs' => $logs,
            'filters' => QueryBuilderHelper::filters($request),
        ]);
    }
}
