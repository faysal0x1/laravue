<?php

declare(strict_types=1);

namespace App\Http\Requests\Todos;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTodoRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'assignee' => ['required', 'string', 'max:255'],
            'due_date' => ['required', 'date'],
        ];
    }
}
