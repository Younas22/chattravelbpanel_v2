<?php

namespace App\Events;

use App\Models\Conversation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewConversation implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Conversation $conversation)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('admin-conversations'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'conversation.new';
    }

    public function broadcastWith(): array
    {
        $visitor = $this->conversation->visitor;
        return [
            'id'           => $this->conversation->id,
            'visitor_id'   => $visitor->id,
            'display_name' => $visitor->display_name,
            'country'      => $visitor->country,
            'current_page' => $visitor->current_page,
            'status'       => $this->conversation->status,
            'created_at'   => $this->conversation->created_at->toISOString(),
        ];
    }
}
