<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Visitor extends Model
{
    public const LEAD_STATUSES = [
        'pending'    => 'Pending',
        'contacted'  => 'Contacted',
        'follow_up'  => 'Follow Up',
        'converted'  => 'Converted',
        'closed'     => 'Closed',
    ];

    protected $fillable = [
        'session_id', 'name', 'email', 'whatsapp', 'ip_address', 'country',
        'country_code', 'city', 'browser', 'os', 'device',
        'current_page', 'landing_page', 'referrer',
        'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content',
        'is_online', 'last_activity_at',
        'lead_status', 'lead_status_updated_at',
    ];

    protected $casts = [
        'is_online' => 'boolean',
        'last_activity_at' => 'datetime',
        'lead_status_updated_at' => 'datetime',
    ];

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function activeConversation(): HasOne
    {
        return $this->hasOne(Conversation::class)->whereIn('status', ['active', 'pending'])->latest();
    }

    public function latestConversation(): HasOne
    {
        return $this->hasOne(Conversation::class)->latest();
    }

    public function hasSubmittedDetails(): bool
    {
        return (bool) ($this->name || $this->email || $this->whatsapp);
    }

    public function getLeadStatusLabelAttribute(): string
    {
        return self::LEAD_STATUSES[$this->lead_status] ?? ucfirst($this->lead_status ?? 'pending');
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
