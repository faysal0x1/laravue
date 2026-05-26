<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class QueryBuilderHelper
{
    public static function apply(Request $request, Builder $query, array $searchable = [], array $sortable = []): Builder
    {
        if ($search = $request->input('search')) {
            $query->where(function (Builder $q) use ($search, $searchable): void {
                foreach ($searchable as $field) {
                    $q->orWhere($field, 'like', "%{$search}%");
                }
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($username = $request->input('username')) {
            $query->where('username', 'like', "%{$username}%");
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        if ($priceMin = $request->input('price_min')) {
            $query->where('budget', '>=', $priceMin);
        }

        if ($priceMax = $request->input('price_max')) {
            $query->where('budget', '<=', $priceMax);
        }

        $sortColumn = $request->input('sort_column', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        if (in_array($sortColumn, $sortable, true)) {
            $query->orderBy($sortColumn, $sortDirection);
        }

        return $query;
    }

    public static function paginate(Request $request, Builder $query)
    {
        $perPage = (int) $request->input('per_page', 10);

        if ($perPage < 1) {
            $perPage = 10;
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public static function filters(Request $request): array
    {
        return $request->only([
            'search',
            'sort_column',
            'sort_direction',
            'per_page',
            'page',
            'status',
            'type',
            'date_from',
            'date_to',
            'price_min',
            'price_max',
            'action',
            'model_type',
        ]);
    }
}
