<?php

declare(strict_types=1);

namespace App\Http\Requests\Todos;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkDestroyTodoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', Rule::exists('todos', 'id')],
        ];
    }

    /**
     * @return array<int, int>
     */
    public function ids(): array
    {
        return array_values(array_unique(array_map(
            fn (int|string $id): int => (int) $id,
            $this->validated('ids'),
        )));
    }
}
