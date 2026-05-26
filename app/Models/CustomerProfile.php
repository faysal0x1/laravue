<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'phone',
    'photo',
    'default_pickup_address',
    'default_pickup_city',
    'default_pickup_lat',
    'default_pickup_lng',
    'notification_preferences',
])]
class CustomerProfile extends Model
{
    #[\Override]
    protected function casts(): array
    {
        return [
            'default_pickup_lat' => 'decimal:7',
            'default_pickup_lng' => 'decimal:7',
            'notification_preferences' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
