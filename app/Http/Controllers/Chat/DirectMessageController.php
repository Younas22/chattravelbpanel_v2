<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Chat\Concerns\HasChatSidebar;
use App\Http\Controllers\Controller;
use App\Models\DirectMessage;
use App\Models\TicketUser;
use Illuminate\Http\Request;

class DirectMessageController extends Controller
{
    use HasChatSidebar;

    public function show(TicketUser $contact)
    {
        if (!auth('ticket_user')->check()) {
            return redirect()->route('tickets.login');
        }

        $user = auth('ticket_user')->user();

        if (!$user->sharesGroupWith($contact->id)) {
            return redirect()->route('tickets.chat.index')->with('error', 'You do not have access to message this user.');
        }

        $messages = DirectMessage::betweenTicketUsers($user->id, $contact->id)->with('replyTo')->orderBy('created_at')->get();

        DirectMessage::where('sender_type', 'ticket_user')->where('sender_id', $contact->id)
            ->where('recipient_type', 'ticket_user')->where('recipient_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $groups = $this->sidebarGroups($user);
        $contacts = $this->sidebarContacts($user);
        $supportUnread = $this->sidebarSupportUnread($user);

        return view('tickets.chat.dm', compact('contact', 'messages', 'groups', 'contacts', 'supportUnread'));
    }

    public function sendMessage(Request $request, TicketUser $contact)
    {
        if (!auth('ticket_user')->check()) {
            return redirect()->route('tickets.login');
        }

        $user = auth('ticket_user')->user();

        if (!$user->sharesGroupWith($contact->id)) {
            return response()->json(['error' => 'You do not have access to message this user.'], 403);
        }

        $request->validate([
            'body'       => 'nullable|string|max:5000',
            'attachment' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf,zip,txt',
        ]);

        if (!$request->body && !$request->hasFile('attachment')) {
            return response()->json(['error' => 'Message or attachment required.'], 422);
        }

        $data = [
            'reply_to_id'    => $request->integer('reply_to_id') ?: null,
            'sender_type'    => 'ticket_user',
            'sender_id'      => $user->id,
            'recipient_type' => 'ticket_user',
            'recipient_id'   => $contact->id,
            'body'           => $request->body,
        ];

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $pairKey = min($user->id, $contact->id) . '-' . max($user->id, $contact->id);
            $path = $file->store('dm-' . $pairKey, 'public_direct');
            $data['attachment_path'] = $path;
            $data['attachment_name'] = $file->getClientOriginalName();
            $data['attachment_mime'] = $file->getMimeType();
            $data['attachment_size'] = $file->getSize();
            $data['attachment_type'] = $this->getAttachmentType($file->getMimeType());
        }

        $message = DirectMessage::create($data);

        return response()->json([
            'message'        => $message,
            'attachment_url' => $message->attachment_url,
        ]);
    }

    public function pollMessages(Request $request, TicketUser $contact)
    {
        if (!auth('ticket_user')->check()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $user = auth('ticket_user')->user();

        if (!$user->sharesGroupWith($contact->id)) {
            return response()->json(['error' => 'You do not have access to message this user.'], 403);
        }

        $afterId = $request->integer('after_id', 0);

        $messages = DirectMessage::betweenTicketUsers($user->id, $contact->id)
            ->with('replyTo')
            ->when($afterId, fn($q) => $q->where('id', '>', $afterId))
            ->orderBy('created_at')
            ->get()
            ->map(fn($m) => [
                'id'              => $m->id,
                'is_mine'         => $m->sender_id === $user->id,
                'sender_name'     => $m->sender_name,
                'body'            => $m->body,
                'attachment_url'  => $m->attachment_url,
                'attachment_name' => $m->attachment_name,
                'attachment_type' => $m->attachment_type,
                'created_at'      => $m->created_at->toISOString(),
                'reply_to'        => $m->replyTo ? [
                    'id'              => $m->replyTo->id,
                    'body'            => $m->replyTo->body,
                    'is_mine'         => $m->replyTo->sender_id === $user->id,
                    'sender_name'     => $m->replyTo->sender_name,
                    'attachment_name' => $m->replyTo->attachment_name,
                ] : null,
            ]);

        if ($messages->isNotEmpty()) {
            DirectMessage::where('sender_type', 'ticket_user')->where('sender_id', $contact->id)
                ->where('recipient_type', 'ticket_user')->where('recipient_id', $user->id)
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }

        return response()->json(['messages' => $messages]);
    }

    public function showSupport()
    {
        if (!auth('ticket_user')->check()) {
            return redirect()->route('tickets.login');
        }

        $user = auth('ticket_user')->user();

        $messages = DirectMessage::betweenAdminAndTicketUser($user->id)->with('replyTo')->orderBy('created_at')->get();

        DirectMessage::where('sender_type', 'admin')
            ->where('recipient_type', 'ticket_user')->where('recipient_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $groups = $this->sidebarGroups($user);
        $contacts = $this->sidebarContacts($user);

        return view('tickets.chat.support', compact('messages', 'groups', 'contacts'));
    }

    // supportUnread is intentionally omitted on the support page itself — opening it marks messages read

    public function sendSupportMessage(Request $request)
    {
        if (!auth('ticket_user')->check()) {
            return redirect()->route('tickets.login');
        }

        $user = auth('ticket_user')->user();

        $request->validate([
            'body'       => 'nullable|string|max:5000',
            'attachment' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf,zip,txt',
        ]);

        if (!$request->body && !$request->hasFile('attachment')) {
            return response()->json(['error' => 'Message or attachment required.'], 422);
        }

        $data = [
            'reply_to_id'    => $request->integer('reply_to_id') ?: null,
            'sender_type'    => 'ticket_user',
            'sender_id'      => $user->id,
            'recipient_type' => 'admin',
            'recipient_id'   => 0,
            'body'           => $request->body,
        ];

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('dm-admin-' . $user->id, 'public_direct');
            $data['attachment_path'] = $path;
            $data['attachment_name'] = $file->getClientOriginalName();
            $data['attachment_mime'] = $file->getMimeType();
            $data['attachment_size'] = $file->getSize();
            $data['attachment_type'] = $this->getAttachmentType($file->getMimeType());
        }

        $message = DirectMessage::create($data);

        return response()->json([
            'message'        => $message,
            'attachment_url' => $message->attachment_url,
        ]);
    }

    public function pollSupportMessages(Request $request)
    {
        if (!auth('ticket_user')->check()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $user = auth('ticket_user')->user();
        $afterId = $request->integer('after_id', 0);

        $messages = DirectMessage::betweenAdminAndTicketUser($user->id)
            ->with('replyTo')
            ->when($afterId, fn($q) => $q->where('id', '>', $afterId))
            ->orderBy('created_at')
            ->get()
            ->map(fn($m) => [
                'id'              => $m->id,
                'is_mine'         => $m->sender_type === 'ticket_user',
                'sender_name'     => $m->sender_name,
                'body'            => $m->body,
                'attachment_url'  => $m->attachment_url,
                'attachment_name' => $m->attachment_name,
                'attachment_type' => $m->attachment_type,
                'created_at'      => $m->created_at->toISOString(),
                'reply_to'        => $m->replyTo ? [
                    'id'              => $m->replyTo->id,
                    'body'            => $m->replyTo->body,
                    'is_mine'         => $m->replyTo->sender_type === 'ticket_user',
                    'sender_name'     => $m->replyTo->sender_name,
                    'attachment_name' => $m->replyTo->attachment_name,
                ] : null,
            ]);

        if ($messages->isNotEmpty()) {
            DirectMessage::where('sender_type', 'admin')
                ->where('recipient_type', 'ticket_user')->where('recipient_id', $user->id)
                ->where('is_read', false)
                ->update(['is_read' => true]);
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
