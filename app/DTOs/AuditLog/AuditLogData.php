<?php

namespace App\DTOs\AuditLog;

use App\Models\AuditLog;

class AuditLogData
{
    public function __construct(
        public int $id,
        public ?int $user_id,
        public string $action,
        public ?string $model_type,
        public ?int $model_id,
        public ?array $old_values,
        public ?array $new_values,
        public ?string $ip_address,
        public ?string $user_agent,
        public string $created_at,
    ) {}

    public static function fromModel(AuditLog $log): self
    {
        return new self(
            id: $log->id,
            user_id: $log->user_id,
            action: $log->action,
            model_type: $log->model_type,
            model_id: $log->model_id,
            old_values: $log->old_values,
            new_values: $log->new_values,
            ip_address: $log->ip_address,
            user_agent: $log->user_agent,
            created_at: (string) $log->created_at?->toDateTimeString(),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'action' => $this->action,
            'model_type' => $this->model_type,
            'model_id' => $this->model_id,
            'old_values' => $this->old_values,
            'new_values' => $this->new_values,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'created_at' => $this->created_at,
        ];
    }
}
