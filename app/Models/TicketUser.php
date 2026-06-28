<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class TicketUser extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'full_name', 'email', 'phone', 'company_name', 'password',
        'widget_token', 'profile_image', 'social_links',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'social_links'      => 'array',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_user')
            ->withPivot('last_read_at')
            ->withTimestamps();
    }

    public function profileImageUrl(): ?string
    {
        if (!$this->profile_image) return null;
        return rtrim(config('app.url'), '/') . '/attachments/' . $this->profile_image;
    }

    public function sharesGroupWith(int $otherId): bool
    {
        return $this->groups()
            ->whereHas('members', fn($q) => $q->where('ticket_users.id', $otherId))
            ->exists();
    }
}
