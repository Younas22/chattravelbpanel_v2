<?php

namespace App\Models;

use App\Models\Concerns\HasPresence;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class TicketUser extends Authenticatable
{
    use Notifiable, HasPresence;

    protected $fillable = [
        'full_name', 'email', 'phone', 'company_name', 'password',
        'widget_token', 'profile_image', 'social_links', 'last_seen_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'social_links'      => 'array',
        'last_seen_at'      => 'datetime',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function profileImageUrl(): ?string
    {
        if (!$this->profile_image) return null;
        return rtrim(config('app.url'), '/') . '/attachments/' . $this->profile_image;
    }
}
