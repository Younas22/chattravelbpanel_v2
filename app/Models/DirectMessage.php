<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DirectMessage extends Model
{
    protected $fillable = [
        'reply_to_id', 'sender_type', 'sender_id', 'recipient_type', 'recipient_id', 'body',
        'attachment_path', 'attachment_name', 'attachment_mime',
        'attachment_size', 'attachment_type', 'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    protected $appends = ['attachment_url', 'formatted_size', 'sender_name'];

    public function scopeBetweenTicketUsers(Builder $query, int $a, int $b): Builder
    {
        return $query->where(function ($q) use ($a, $b) {
            $q->where('sender_type', 'ticket_user')->where('sender_id', $a)
              ->where('recipient_type', 'ticket_user')->where('recipient_id', $b);
        })->orWhere(function ($q) use ($a, $b) {
            $q->where('sender_type', 'ticket_user')->where('sender_id', $b)
              ->where('recipient_type', 'ticket_user')->where('recipient_id', $a);
        });
    }

    public function scopeBetweenAdminAndTicketUser(Builder $query, int $ticketUserId): Builder
    {
        return $query->where(function ($q) use ($ticketUserId) {
            $q->where('sender_type', 'ticket_user')->where('sender_id', $ticketUserId)
              ->where('recipient_type', 'admin');
        })->orWhere(function ($q) use ($ticketUserId) {
            $q->where('recipient_type', 'ticket_user')->where('recipient_id', $ticketUserId)
              ->where('sender_type', 'admin');
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

    public function getSenderNameAttribute(): string
    {
        if ($this->sender_type === 'admin') {
            return optional(User::find($this->sender_id))->name ?? 'Support Team';
        }
        return optional(TicketUser::find($this->sender_id))->full_name ?? 'Member';
    }

    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(DirectMessage::class, 'reply_to_id');
    }
}
