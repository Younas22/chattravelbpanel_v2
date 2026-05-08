<?php

namespace App\Events;

use App\Models\Visitor;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VisitorStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Visitor $visitor, public string $event)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('admin-visitors'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'visitor.status';
    }

    public function broadcastWith(): array
    {
        return [
            'id'           => $this->visitor->id,
            'session_id'   => $this->visitor->session_id,
            'display_name' => $this->visitor->display_name,
            'country'      => $this->visitor->country,
            'current_page' => $this->visitor->current_page,
            'is_online'    => $this->visitor->is_online,
            'event'        => $this->event,
        ];
    }
}
