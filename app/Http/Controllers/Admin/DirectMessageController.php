<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DirectMessage;
use App\Models\TicketUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DirectMessageController extends Controller
{
    public function index(Request $request)
    {
        $threads = $this->threads($request->search);

        if ($request->wantsJson()) {
            return response()->json([
                'threads' => $threads->map(fn($t) => [
                    'id'                => $t->id,
                    'full_name'         => $t->full_name,
                    'email'             => $t->email,
                    'profile_image_url' => $t->profileImageUrl(),
                    'last_message'      => $t->last_message?->body,
                    'unread_count'      => $t->unread_count,
                    'is_online'         => $t->isOnline(),
                ]),
            ]);
        }

        return view('admin.messages.index', compact('threads'));
    }

    public function show(Request $request, TicketUser $ticketUser)
    {
        $messages = DirectMessage::betweenAdminAndTicketUser($ticketUser->id)->with('replyTo')->orderBy('created_at')->get();

        DirectMessage::where('sender_type', 'ticket_user')->where('sender_id', $ticketUser->id)
            ->where('recipient_type', 'admin')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        if ($request->wantsJson()) {
            return response()->json([
                'contact' => [
                    'id'                => $ticketUser->id,
                    'full_name'         => $ticketUser->full_name,
                    'email'             => $ticketUser->email,
                    'profile_image_url' => $ticketUser->profileImageUrl(),
                    'is_online'         => $ticketUser->isOnline(),
                ],
                'messages' => $messages->map(fn($m) => $m->toApiArray()),
            ]);
        }

        $threads = $this->threads();

        return view('admin.messages.show', compact('ticketUser', 'messages', 'threads'));
    }

    public function sendMessage(Request $request, TicketUser $ticketUser)
    {
        $request->validate([
            'body'            => 'nullable|string|max:5000',
            'attachment'      => 'nullable|file|max:20480|mimes:jpg,jpeg,png,gif,webp,pdf,xml,zip,mp4,txt',
            'idempotency_key' => 'nullable|string|max:64',
        ]);

        if (!$request->body && !$request->hasFile('attachment')) {
            return response()->json(['error' => 'Message or attachment required.'], 422);
        }

        if ($request->idempotency_key) {
            $existing = DirectMessage::where('idempotency_key', $request->idempotency_key)->first();
            if ($existing) {
                return response()->json([
                    'message'        => $existing,
                    'attachment_url' => $existing->attachment_url,
                ]);
            }
        }

        $data = [
            'reply_to_id'     => $request->integer('reply_to_id') ?: null,
            'sender_type'     => 'admin',
            'sender_id'       => auth()->id(),
            'recipient_type'  => 'ticket_user',
            'recipient_id'    => $ticketUser->id,
            'body'            => $request->body,
            'idempotency_key' => $request->idempotency_key,
        ];

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('dm-admin-' . $ticketUser->id, 'public_direct');
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

    public function pollMessages(Request $request, TicketUser $ticketUser)
    {
        $afterId = $request->integer('after_id', 0);

        $messages = DirectMessage::betweenAdminAndTicketUser($ticketUser->id)
            ->with('replyTo')
            ->when($afterId, fn($q) => $q->where('id', '>', $afterId))
            ->orderBy('created_at')
            ->get()
            ->map(fn($m) => $m->toApiArray());

        if ($messages->isNotEmpty()) {
            DirectMessage::where('sender_type', 'ticket_user')->where('sender_id', $ticketUser->id)
                ->where('recipient_type', 'admin')
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }

        return response()->json(['messages' => $messages]);
    }

    private function threads(?string $search = null)
    {
        $ticketUserIds = DirectMessage::where(function ($q) {
                $q->where('sender_type', 'ticket_user')->where('recipient_type', 'admin');
            })->orWhere(function ($q) {
                $q->where('sender_type', 'admin')->where('recipient_type', 'ticket_user');
            })
            ->get()
            ->map(fn($m) => $m->sender_type === 'admin' ? $m->recipient_id : $m->sender_id)
            ->unique();

        $query = TicketUser::whereIn('id', $ticketUserIds);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->get()
            ->map(function ($tu) {
                $tu->last_message = DirectMessage::betweenAdminAndTicketUser($tu->id)->latest()->first();
                $tu->unread_count = DirectMessage::where('sender_type', 'ticket_user')->where('sender_id', $tu->id)
                    ->where('recipient_type', 'admin')->where('is_read', false)->count();
                return $tu;
            })
            ->sortByDesc(fn($tu) => $tu->last_message?->created_at)
            ->values();
    }

    private function getAttachmentType(string $mime): string
    {
        if (str_starts_with($mime, 'image/')) return 'image';
        if (str_starts_with($mime, 'video/')) return 'video';
        if (in_array($mime, ['application/zip', 'application/x-rar-compressed'])) return 'archive';
        return 'document';
    }
}
