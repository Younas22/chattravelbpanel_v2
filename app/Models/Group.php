<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Group extends Model
{
    protected $fillable = [
        'name', 'description', 'profile_image', 'created_by', 'unread_admin',
    ];

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(TicketUser::class, 'group_user')
            ->withPivot('last_read_at')
            ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(GroupMessage::class)->orderBy('created_at');
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(GroupMessage::class)->latestOfMany();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function profileImageUrl(): ?string
    {
        if (!$this->profile_image) return null;
        return rtrim(config('app.url'), '/') . '/attachments/' . $this->profile_image;
    }
}
