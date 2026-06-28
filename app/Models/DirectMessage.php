<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DirectMessage extends Model
{
    protected $fillable = [
        'sender_id', 'recipient_id', 'body',
        'attachment_path', 'attachment_name', 'attachment_mime',
        'attachment_size', 'attachment_type', 'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    protected $appends = ['attachment_url', 'formatted_size'];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(TicketUser::class, 'sender_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(TicketUser::class, 'recipient_id');
    }

    public function scopeBetween(Builder $query, int $a, int $b): Builder
    {
        return $query->where(function ($q) use ($a, $b) {
            $q->where('sender_id', $a)->where('recipient_id', $b);
        })->orWhere(function ($q) use ($a, $b) {
            $q->where('sender_id', $b)->where('recipient_id', $a);
        });
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        if (!$this->attachment_path) return null;
        $base = rtrim(config('app.url'), '/');
        return $base . '/attachments/' . $this->attachment_path;
    }

    public function getFormattedSizeAttribute(): ?string
    {
        if (!$this->attachment_size) return null;
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->attachment_size;
        $i = 0;
        while ($size >= 1024 && $i < 3) {
            $size /= 1024;
            $i++;
        }
        return round($size, 2) . ' ' . $units[$i];
    }
}
