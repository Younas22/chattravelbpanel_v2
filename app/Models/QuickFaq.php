<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuickFaq extends Model
{
    protected $fillable = [
        'question', 'answer', 'sort_order', 'is_active', 'show_chat_button',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_chat_button' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
