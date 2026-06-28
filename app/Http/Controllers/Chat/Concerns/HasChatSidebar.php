<?php

namespace App\Http\Controllers\Chat\Concerns;

use App\Models\DirectMessage;
use App\Models\TicketUser;

trait HasChatSidebar
{
    protected function sidebarGroups(TicketUser $user)
    {
        return $user->groups()
            ->withCount('members')
            ->with('latestMessage')
            ->orderByDesc('updated_at')
            ->get()
            ->map(function ($group) {
                $since = $group->pivot->last_read_at;
                $group->unread_count = $group->messages()
                    ->where('sender_type', 'admin')
                    ->when($since, fn($q) => $q->where('created_at', '>', $since))
                    ->count();
                return $group;
            });
    }

    protected function sidebarContacts(TicketUser $user)
    {
        $groupIds = $user->groups()->pluck('groups.id');

        return TicketUser::whereHas('groups', fn($q) => $q->whereIn('groups.id', $groupIds))
            ->where('id', '!=', $user->id)
            ->orderBy('full_name')
            ->get()
            ->map(function ($contact) use ($user) {
                $contact->last_message = DirectMessage::betweenTicketUsers($user->id, $contact->id)
                    ->latest()
                    ->first();
                $contact->unread_count = DirectMessage::where('sender_type', 'ticket_user')->where('sender_id', $contact->id)
                    ->where('recipient_type', 'ticket_user')->where('recipient_id', $user->id)
                    ->where('is_read', false)
                    ->count();
                return $contact;
            });
    }

    protected function sidebarSupportUnread(TicketUser $user): int
    {
        return DirectMessage::where('sender_type', 'admin')
            ->where('recipient_type', 'ticket_user')->where('recipient_id', $user->id)
            ->where('is_read', false)
            ->count();
    }
}
