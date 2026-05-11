<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlobalOffer extends Model
{
    protected $fillable = ['label', 'original_price', 'discount_price', 'is_active'];

    protected $casts = [
        'original_price'  => 'decimal:2',
        'discount_price'  => 'decimal:2',
        'is_active'       => 'boolean',
    ];
}
