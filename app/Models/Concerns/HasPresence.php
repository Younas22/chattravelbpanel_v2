<?php

namespace App\Models\Concerns;

trait HasPresence
{
    /** Online if seen within the last 45s - long enough to survive a missed
     *  poll cycle, short enough that a closed/crashed client reads as offline
     *  quickly without needing a scheduled sweep job. */
    public function isOnline(): bool
    {
        return $this->last_seen_at !== null
            && $this->last_seen_at->greaterThan(now()->subSeconds(45));
    }
}
