<?php

namespace App\Traits;

trait BelongsToShop
{
    protected static function bootBelongsToShop(): void
    {
        static::creating(function ($model) {
            if (auth()->check() && ! $model->shop_id) {
                $model->shop_id = auth()->user()->shop_id;
            }
        });
    }

    public function scopeCurrentShop($query)
    {
        return $query->where('shop_id', auth()->user()->shop_id);
    }
}
