<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Modules\Cache\Traits\HasModelCache;

#[Fillable(['name', 'icon', 'link', 'position', 'status'])]
class SocialLink extends Model
{
    use HasModelCache;

    #[\Override]
    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'position' => 'integer',
        ];
    }
}
