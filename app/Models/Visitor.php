<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Visitor extends Model
{
    protected $fillable = [
        'session_id', 'name', 'email', 'ip_address', 'country',
        'country_code', 'city', 'browser', 'os', 'device',
        'current_page', 'landing_page', 'referrer',
        'is_online', 'last_activity_at',
    ];

    protected $casts = [
        'is_online' => 'boolean',
        'last_activity_at' => 'datetime',
    ];

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function activeConversation(): HasOne
    {
        return $this->hasOne(Conversation::class)->whereIn('status', ['active', 'pending'])->latest();
    }

    public function logs(): HasMany
    {
        return $this->hasMany(VisitorLog::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name ?: ('Visitor #' . $this->id);
    }

    public function markOnline(): void
    {
        $this->update(['is_online' => true, 'last_activity_at' => now()]);
    }

    public function markOffline(): void
    {
        $this->update(['is_online' => false]);
    }
}
