<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Modules\Cache\Traits\HasModelCache;

#[Fillable(['title', 'description', 'assignee', 'due_date', 'done'])]
class Todo extends Model
{
    use HasModelCache;

    #[\Override]
    protected function casts(): array
    {
        return [
            'done' => 'boolean',
            'due_date' => 'date:Y-m-d',
        ];
    }
}
