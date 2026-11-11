<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\DirectMessage;
use App\Services\PushNotifier;
use Illuminate\Http\Request;

class DirectMessageController extends Controller
{
    public function showSupport(Request $request)
    {
        if (!auth('ticket_user')->check()) {
            return $request->wantsJson()
                ? response()->json(['error' => 'Unauthenticated.'], 401)
                : redirect()->route('tickets.login');
        }

        $user = auth('ticket_user')->user();

        $messages = DirectMessage::betweenAdminAndTicketUser($user->id)->with('replyTo')->orderBy('created_at')->get();

        DirectMessage::where('sender_type', 'admin')
            ->where('recipient_type', 'ticket_user')->where('recipient_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        if ($request->wantsJson()) {
            return response()->json([
                'messages' => $messages->map(fn($m) => $m->toApiArray($user->id, 'ticket_user')),
            ]);
        }

        // supportUnread is intentionally omitted on the support page itself — opening it marks messages read
        return view('tickets.chat.support', compact('messages'));
    }

    public function sendSupportMessage(Request $request)
    {
        if (!auth('ticket_user')->check()) {
            return redirect()->route('tickets.login');
        }

        $user = auth('ticket_user')->user();

        $request->validate([
            'body'            => 'nullable|string|max:5000',
            'attachment'      => 'nullable|file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf,zip,txt',
            'idempotency_key' => 'nullable|string|max:64',
        ]);

        if (!$request->body && !$request->hasFile('attachment')) {
            return response()->json(['error' => 'Message or attachment required.'], 422);
        }

        if ($request->idempotency_key) {
            $existing = DirectMessage::where('idempotency_key', $request->idempotency_key)->first();
            if ($existing) {
                return response()->json([
                    'message'        => $existing->toApiArray($user->id, 'ticket_user'),
                    'attachment_url' => $existing->attachment_url,
                ]);
            }
        }

        $data = [
            'reply_to_id'     => $request->integer('reply_to_id') ?: null,
            'sender_type'     => 'ticket_user',
            'sender_id'       => $user->id,
            'recipient_type'  => 'admin',
            'recipient_id'    => 0,
            'body'            => $request->body,
            'idempotency_key' => $request->idempotency_key,
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

        PushNotifier::notifyAdmins(
            '✉️ ' . $user->full_name,
            $message->body ?: '📎 ' . ($message->attachment_name ?: 'Attachment'),
            route('admin.messages.show', $user),
            'dm-' . $user->id
        );

        return response()->json([
            'message'        => $message->toApiArray($user->id, 'ticket_user'),
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
            ->map(fn($m) => $m->toApiArray($user->id, 'ticket_user'));

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
