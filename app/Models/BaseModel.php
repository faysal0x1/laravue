<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BaseModel extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    /**
     * Get formatted amount with currency
     */
    public function formatAmount($amount, $currency = 'BDT')
    {
        $symbols = [
            'BDT' => '৳',
            'USD' => '$',
            'EUR' => '€',
            'INR' => '₹',
            'AED' => 'د.إ',
        ];

        return $symbols[$currency].' '.number_format($amount, 2);
    }
}
