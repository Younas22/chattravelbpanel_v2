<?php

namespace App\Events;

use App\Models\GroupMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GroupMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public GroupMessage $message)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('group.' . $this->message->group_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'group.message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'id'              => $this->message->id,
            'group_id'        => $this->message->group_id,
            'sender_type'     => $this->message->sender_type,
            'sender_id'       => $this->message->sender_id,
            'sender_name'     => $this->message->sender_name,
            'body'            => $this->message->body,
            'attachment_url'  => $this->message->attachment_url,
            'attachment_name' => $this->message->attachment_name,
            'attachment_type' => $this->message->attachment_type,
            'created_at'      => $this->message->created_at->toISOString(),
        ];
    }
}
