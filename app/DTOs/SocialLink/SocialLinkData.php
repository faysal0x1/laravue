<?php

namespace App\DTOs\SocialLink;

use App\Models\SocialLink;

class SocialLinkData
{
    public function __construct(
        public int $id,
        public string $name,
        public string $icon,
        public string $link,
        public int $position,
        public bool $status,
        public string $created_at,
    ) {}

    public static function fromModel(SocialLink $socialLink): self
    {
        return new self(
            id: $socialLink->id,
            name: $socialLink->name,
            icon: $socialLink->icon,
            link: $socialLink->link,
            position: $socialLink->position,
            status: $socialLink->status,
            created_at: $socialLink->created_at->format('Y-m-d'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'icon' => $this->icon,
            'link' => $this->link,
            'position' => $this->position,
            'status' => $this->status,
            'created_at' => $this->created_at,
        ];
    }
}
