<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
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

    public function profileImageUrl(): ?string
    {
        if (!$this->profile_image) return null;
        return asset('storage/' . $this->profile_image);
    }
}
