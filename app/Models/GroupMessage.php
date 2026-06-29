<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupMessage extends Model
{
    protected $fillable = [
        'group_id', 'reply_to_id', 'sender_type', 'sender_id', 'body', 'idempotency_key',
        'attachment_path', 'attachment_name', 'attachment_mime',
        'attachment_size', 'attachment_type',
    ];

    protected $appends = ['attachment_url', 'formatted_size', 'sender_name', 'sender_avatar'];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(GroupMessage::class, 'reply_to_id');
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

    public function getSenderAvatarAttribute(): ?string
    {
        if ($this->sender_type === 'admin') {
            return optional(User::find($this->sender_id))->avatar_url;
        }
        return optional(TicketUser::find($this->sender_id))->profileImageUrl();
    }

    /** Shared shape used by both the poll endpoints and the JSON form of the
     *  initial page load, so API clients (e.g. ChatDesktop) see identical data
     *  whichever path they came from. */
    public function toApiArray(): array
    {
        return [
            'id'              => $this->id,
            'sender_type'     => $this->sender_type,
            'sender_id'       => $this->sender_id,
            'sender_name'     => $this->sender_name,
            'sender_avatar'   => $this->sender_avatar,
            'body'            => $this->body,
            'attachment_url'  => $this->attachment_url,
            'attachment_name' => $this->attachment_name,
            'attachment_type' => $this->attachment_type,
            'created_at'      => $this->created_at->toISOString(),
            'reply_to'        => $this->replyTo ? [
                'id'              => $this->replyTo->id,
                'body'            => $this->replyTo->body,
                'sender_type'     => $this->replyTo->sender_type,
                'sender_id'       => $this->replyTo->sender_id,
                'sender_name'     => $this->replyTo->sender_name,
                'attachment_name' => $this->replyTo->attachment_name,
            ] : null,
        ];
    }
}
