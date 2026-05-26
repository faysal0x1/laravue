<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'scoreable_type',
    'scoreable_id',
    'risk_score',
    'risk_band',
    'factors',
    'engine_version',
    'assessed_at',
])]
class FraudRiskScore extends Model
{
    public $timestamps = false;

    #[\Override]
    protected function casts(): array
    {
        return [
            'factors' => 'array',
            'assessed_at' => 'datetime',
        ];
    }

    public function scoreable(): MorphTo
    {
        return $this->morphTo();
    }
}
