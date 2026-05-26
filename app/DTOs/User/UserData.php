<?php

namespace App\DTOs\User;

use App\Models\User;

class UserData
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public string $joined_at,
        public bool $is_active,
    ) {}

    public static function fromModel(User $user): self
    {
        return new self(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            joined_at: (string) $user->created_at?->toDateString(),
            is_active: (bool) $user->is_active,
        );
    }

    /**
     * @return array<string, int|string>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'joined_at' => $this->joined_at,
            'status' => $this->is_active ? 'Active' : 'Inactive',
        ];
    }
}
