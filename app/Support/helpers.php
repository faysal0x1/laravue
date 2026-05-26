<?php

use App\Models\AuditLog;
use App\Services\ResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Cache\Contracts\CacheServiceInterface;

if (! function_exists('model_cache')) {
    function model_cache(
        string $modelClass,
        int $seconds,
        callable $callback,
        ?string $key = null,
        ?string $namespace = null,
    ): mixed {
        return resolve(CacheServiceInterface::class)->rememberModel(
            modelClass: $modelClass,
            seconds: $seconds,
            callback: $callback,
            key: $key,
            namespace : $namespace,
        );
    }
}

if (! function_exists('success_response')) {
    function success_response(string $message, int $statusCode = 200): RedirectResponse
    {
        return ResponseService::success($message, $statusCode);
    }
}

if (! function_exists('error_response')) {
    function error_response(string $message, int $statusCode = 422): RedirectResponse
    {
        return ResponseService::error($message, $statusCode);
    }
}

if (! function_exists('success_route')) {
    function success_route(string $route, string $message, array $parameters = [], int $statusCode = 200): RedirectResponse
    {
        return ResponseService::successRoute($route, $message, $parameters, $statusCode);
    }
}

if (! function_exists('error_route')) {
    function error_route(string $route, string $message, array $parameters = [], int $statusCode = 422): RedirectResponse
    {
        return ResponseService::errorRoute($route, $message, $parameters, $statusCode);
    }
}

if (! function_exists('api_success')) {
    /**
     * @param  array<string, mixed>|null  $data
     */
    function api_success(string $message = 'Success', ?array $data = null, int $statusCode = 200): JsonResponse
    {
        return ResponseService::apiSuccess($message, $data, $statusCode);
    }
}

if (! function_exists('api_error')) {
    /**
     * @param  array<string, mixed>|null  $errors
     */
    function api_error(
        string $message = 'Something went wrong',
        int $statusCode = 422,
        ?array $errors = null,
    ): JsonResponse {
        return ResponseService::apiError($message, $statusCode, $errors);
    }
}

if (! function_exists('audit_log')) {
    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    function audit_log(
        string $action,
        ?string $modelType = null,
        int|string|null $modelId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?Request $request = null,
    ): void {
        $request ??= request();
        $user = $request?->user();

        AuditLog::query()->create([
            'user_id' => $user?->id,
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId !== null ? (int) $modelId : null,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }
}
