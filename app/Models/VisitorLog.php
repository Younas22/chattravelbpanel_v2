<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitorLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['visitor_id', 'page_url', 'page_title', 'visited_at'];

    protected $casts = [
        'visited_at' => 'datetime',
    ];

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }
}
