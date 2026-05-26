<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'user_id',
    'event_code',
    'severity',
    'status',
    'subject_type',
    'subject_id',
    'ip_address',
    'user_agent',
    'metadata',
    'detected_at',
    'resolved_at',
    'resolved_by',
])]
class FraudEvent extends Model
{
    #[\Override]
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'detected_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
