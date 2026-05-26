<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'status',
])]
class Shop extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
