<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class ResponseService
{
    /**
     * @param  array{type:string,message:string}  $payload
     */
    private static function flashToast(array $payload): void
    {
        Inertia::flash('toast', $payload);
        session()->flash('toast', $payload);
    }

    public static function success(string $message, int $statusCode = 200): RedirectResponse
    {
        self::flashToast([
            'type' => 'success',
            'message' => $message,
        ]);

        return back();
    }

    public static function error(string $message, int $statusCode = 422): RedirectResponse
    {
        self::flashToast([
            'type' => 'error',
            'message' => $message,
        ]);

        return back();
    }

    public static function successRoute(string $route, string $message, array $parameters = [], int $statusCode = 200): RedirectResponse
    {
        self::flashToast([
            'type' => 'success',
            'message' => $message,
        ]);

        return to_route($route, $parameters);
    }

    public static function errorRoute(string $route, string $message, array $parameters = [], int $statusCode = 422): RedirectResponse
    {
        self::flashToast([
            'type' => 'error',
            'message' => $message,
        ]);

        return to_route($route, $parameters);
    }

    /**
     * @param  array<string, mixed>|null  $data
     */
    public static function apiSuccess(
        string $message = 'Success',
        ?array $data = null,
        int $statusCode = 200,
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * @param  array<string, mixed>|null  $errors
     */
    public static function apiError(
        string $message = 'Something went wrong',
        int $statusCode = 422,
        ?array $errors = null,
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }
}
