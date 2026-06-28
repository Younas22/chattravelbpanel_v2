<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupMessage extends Model
{
    protected $fillable = [
        'group_id', 'sender_type', 'sender_id', 'body',
        'attachment_path', 'attachment_name', 'attachment_mime',
        'attachment_size', 'attachment_type',
    ];

    protected $appends = ['attachment_url', 'formatted_size', 'sender_name'];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
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
            return optional(User::find($this->sender_id))->name ?? 'Admin';
        }
        return optional(TicketUser::find($this->sender_id))->full_name ?? 'Member';
    }
}
