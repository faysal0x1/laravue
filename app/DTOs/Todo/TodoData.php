<?php

namespace App\DTOs\Todo;

use App\Models\Todo;

class TodoData
{
    public function __construct(
        public int $id,
        public string $title,
        public string $description,
        public string $assignee,
        public string $due_date,
        public bool $done,
        public string $created_at,
    ) {}

    public static function fromModel(Todo $todo): self
    {
        return new self(
            id: $todo->id,
            title: $todo->title,
            description: $todo->description ?? '',
            assignee: $todo->assignee,
            due_date: $todo->due_date->format('Y-m-d'),
            done: $todo->done,
            created_at: $todo->created_at->format('Y-m-d'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'assignee' => $this->assignee,
            'due_date' => $this->due_date,
            'done' => $this->done,
            'created_at' => $this->created_at,
        ];
    }
}
