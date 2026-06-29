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

    public function index(Request $request)
    {
        if (!auth('ticket_user')->check()) {
            return $request->wantsJson()
                ? response()->json(['error' => 'Unauthenticated.'], 401)
                : redirect()->route('tickets.login');
        }

        $user = auth('ticket_user')->user();
        $groups = $this->sidebarGroups($user);
        $contacts = $this->sidebarContacts($user);
        $supportUnread = $this->sidebarSupportUnread($user);

        if ($request->wantsJson()) {
            return response()->json([
                'groups' => $groups->map(fn($g) => [
                    'id'                => $g->id,
                    'name'              => $g->name,
                    'profile_image_url' => $g->profileImageUrl(),
                    'members_count'     => $g->members_count,
                    'unread_count'      => $g->unread_count,
                    'last_message'      => $g->latestMessage?->body,
                    'updated_at'        => $g->updated_at->toISOString(),
                ]),
                'contacts' => $contacts->map(fn($c) => [
                    'id'                => $c->id,
                    'full_name'         => $c->full_name,
                    'profile_image_url' => $c->profileImageUrl(),
                    'unread_count'      => $c->unread_count,
                    'last_message'      => $c->last_message?->body,
                ]),
                'support_unread' => $supportUnread,
            ]);
        }

        return view('tickets.chat.index', compact('groups', 'contacts', 'supportUnread'));
    }

    public function show(Request $request, Group $group)
    {
        if (!auth('ticket_user')->check()) {
            return $request->wantsJson()
                ? response()->json(['error' => 'Unauthenticated.'], 401)
                : redirect()->route('tickets.login');
        }

        $user = auth('ticket_user')->user();

        if (!$group->members()->where('ticket_users.id', $user->id)->exists()) {
            return $request->wantsJson()
                ? response()->json(['error' => 'You do not have access to this group.'], 403)
                : redirect()->route('tickets.chat.index')->with('error', 'You do not have access to this group.');
        }

        $group->load(['members', 'messages.replyTo']);
        $group->members()->updateExistingPivot($user->id, ['last_read_at' => now()]);

        if ($request->wantsJson()) {
            return response()->json([
                'group' => [
                    'id'                => $group->id,
                    'name'              => $group->name,
                    'description'       => $group->description,
                    'profile_image_url' => $group->profileImageUrl(),
                ],
                'members' => $group->members->map(fn($m) => [
                    'id'                => $m->id,
                    'full_name'         => $m->full_name,
                    'email'             => $m->email,
                    'profile_image_url' => $m->profileImageUrl(),
                ]),
                'messages' => $group->messages->map(fn($m) => $m->toApiArray()),
            ]);
        }

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
            'body'            => 'nullable|string|max:5000',
            'attachment'      => 'nullable|file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf,zip,txt',
            'idempotency_key' => 'nullable|string|max:64',
        ]);

        if (!$request->body && !$request->hasFile('attachment')) {
            return response()->json(['error' => 'Message or attachment required.'], 422);
        }

        if ($request->idempotency_key) {
            $existing = GroupMessage::where('idempotency_key', $request->idempotency_key)->first();
            if ($existing) {
                return response()->json([
                    'message'        => $existing,
                    'attachment_url' => $existing->attachment_url,
                ]);
            }
        }

        $data = [
            'group_id'        => $group->id,
            'reply_to_id'     => $request->integer('reply_to_id') ?: null,
            'sender_type'     => 'ticket_user',
            'sender_id'       => $user->id,
            'body'            => $request->body,
            'idempotency_key' => $request->idempotency_key,
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
            ->with('replyTo')
            ->when($afterId, fn($q) => $q->where('id', '>', $afterId))
            ->get()
            ->map(fn($m) => $m->toApiArray());

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
