<?php

namespace App\Http\Controllers\Chat;

use App\Events\GroupMessageSent;
use App\Http\Controllers\Chat\Concerns\HasChatSidebar;
use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GroupChatController extends Controller
{
    use HasChatSidebar;

    public function index()
    {
        if (!auth('ticket_user')->check()) {
            return redirect()->route('tickets.login');
        }

        $user = auth('ticket_user')->user();
        $groups = $this->sidebarGroups($user);
        $contacts = $this->sidebarContacts($user);
        $supportUnread = $this->sidebarSupportUnread($user);

        return view('tickets.chat.index', compact('groups', 'contacts', 'supportUnread'));
    }

    public function show(Group $group)
    {
        if (!auth('ticket_user')->check()) {
            return redirect()->route('tickets.login');
        }

        $user = auth('ticket_user')->user();

        if (!$group->members()->where('ticket_users.id', $user->id)->exists()) {
            return redirect()->route('tickets.chat.index')->with('error', 'You do not have access to this group.');
        }

        $group->load(['members', 'messages']);
        $group->members()->updateExistingPivot($user->id, ['last_read_at' => now()]);

        $groups = $this->sidebarGroups($user);
        $contacts = $this->sidebarContacts($user);
        $supportUnread = $this->sidebarSupportUnread($user);

        return view('tickets.chat.show', compact('group', 'groups', 'contacts', 'supportUnread'));
    }

    public function sendMessage(Request $request, Group $group)
    {
        if (!auth('ticket_user')->check()) {
            return redirect()->route('tickets.login');
        }

        $user = auth('ticket_user')->user();

        if (!$group->members()->where('ticket_users.id', $user->id)->exists()) {
            return response()->json(['error' => 'You do not have access to this group.'], 403);
        }

        $request->validate([
            'body'       => 'nullable|string|max:5000',
            'attachment' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf,zip,txt',
        ]);

        if (!$request->body && !$request->hasFile('attachment')) {
            return response()->json(['error' => 'Message or attachment required.'], 422);
        }

        $data = [
            'group_id'    => $group->id,
            'sender_type' => 'ticket_user',
            'sender_id'   => $user->id,
            'body'        => $request->body,
        ];

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('group-' . $group->id, 'public_direct');
            $data['attachment_path'] = $path;
            $data['attachment_name'] = $file->getClientOriginalName();
            $data['attachment_mime'] = $file->getMimeType();
            $data['attachment_size'] = $file->getSize();
            $data['attachment_type'] = $this->getAttachmentType($file->getMimeType());
        }

        $message = GroupMessage::create($data);
        $group->increment('unread_admin');
        $group->touch();
        $group->members()->updateExistingPivot($user->id, ['last_read_at' => now()]);

        try {
            broadcast(new GroupMessageSent($message))->toOthers();
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json([
            'message'        => $message,
            'attachment_url' => $message->attachment_url,
        ]);
    }

    public function pollMessages(Request $request, Group $group)
    {
        if (!auth('ticket_user')->check()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $user = auth('ticket_user')->user();

        if (!$group->members()->where('ticket_users.id', $user->id)->exists()) {
            return response()->json(['error' => 'You do not have access to this group.'], 403);
        }

        $afterId = $request->integer('after_id', 0);

        $messages = $group->messages()
            ->when($afterId, fn($q) => $q->where('id', '>', $afterId))
            ->get()
            ->map(fn($m) => [
                'id'              => $m->id,
                'sender_type'     => $m->sender_type,
                'sender_name'     => $m->sender_name,
                'body'            => $m->body,
                'attachment_url'  => $m->attachment_url,
                'attachment_name' => $m->attachment_name,
                'attachment_type' => $m->attachment_type,
                'created_at'      => $m->created_at->toISOString(),
            ]);

        if ($messages->isNotEmpty()) {
            $group->members()->updateExistingPivot($user->id, ['last_read_at' => now()]);
        }

        return response()->json(['messages' => $messages]);
    }

    private function getAttachmentType(string $mime): string
    {
        if (str_starts_with($mime, 'image/')) return 'image';
        if (str_starts_with($mime, 'video/')) return 'video';
        if (in_array($mime, ['application/zip', 'application/x-rar-compressed'])) return 'archive';
        return 'document';
    }
}
