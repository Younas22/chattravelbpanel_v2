<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketMessage extends Model
{
    protected $fillable = [
        'ticket_id', 'sender_type', 'sender_id', 'body',
        'attachment_path', 'attachment_name', 'attachment_mime', 'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    protected $appends = ['attachment_url'];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        if (!$this->attachment_path) return null;
        return rtrim(config('app.url'), '/') . '/attachments/' . $this->attachment_path;
    }
}
