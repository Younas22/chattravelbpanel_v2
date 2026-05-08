<?php

namespace App\Http\Controllers\Admin;

use App\Events\MessageSent;
use App\Events\TypingIndicator;
use App\Http\Controllers\Controller;
use App\Models\CannedReply;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ConversationController extends Controller
{
    public function index(Request $request)
    {
        $query = Conversation::with(['visitor', 'latestMessage'])
            ->orderByDesc('updated_at');

        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $query->whereHas('visitor', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('ip_address', 'like', "%{$request->search}%");
            });
        }

        $conversations = $query->paginate(20);
        $cannedReplies = CannedReply::orderBy('title')->get();

        return view('admin.conversations.index', compact('conversations', 'cannedReplies'));
    }

    public function show(Conversation $conversation)
    {
        $conversation->load(['visitor.logs', 'messages', 'assignedAgent']);

        // Mark all visitor messages as read
        $conversation->messages()
            ->where('sender_type', 'visitor')
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        $conversation->update(['unread_admin' => 0]);

        $cannedReplies = CannedReply::orderBy('title')->get();

        return view('admin.conversations.show', compact('conversation', 'cannedReplies'));
    }

    public function sendMessage(Request $request, Conversation $conversation)
    {
        $request->validate([
            'body'       => 'nullable|string|max:5000',
            'attachment' => 'nullable|file|max:20480|mimes:jpg,jpeg,png,gif,webp,pdf,xml,zip,mp4,txt',
        ]);

        if (!$request->body && !$request->hasFile('attachment')) {
            return response()->json(['error' => 'Message or attachment required.'], 422);
        }

        $data = [
            'conversation_id' => $conversation->id,
            'sender_type'     => 'admin',
            'sender_id'       => auth()->id(),
            'body'            => $request->body,
        ];

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store((string) $conversation->id, 'public_direct');
            $data['attachment_path'] = $path;
            $data['attachment_name'] = $file->getClientOriginalName();
            $data['attachment_mime'] = $file->getMimeType();
            $data['attachment_size'] = $file->getSize();
            $data['attachment_type'] = $this->getAttachmentType($file->getMimeType());
        }

        $message = Message::create($data);

        if ($conversation->status === 'pending') {
            $conversation->update(['status' => 'active']);
        }

        $conversation->increment('unread_visitor');
        $conversation->touch();

        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'message' => $message->load('sender'),
            'attachment_url' => $message->attachment_url,
        ]);
    }

    public function pollMessages(Request $request, Conversation $conversation)
    {
        $afterId = $request->integer('after_id', 0);

        $messages = $conversation->messages()
            ->when($afterId, fn($q) => $q->where('id', '>', $afterId))
            ->get()
            ->map(fn($m) => [
                'id'              => $m->id,
                'sender_type'     => $m->sender_type,
                'body'            => $m->body,
                'attachment_url'  => $m->attachment_url,
                'attachment_name' => $m->attachment_name,
                'attachment_type' => $m->attachment_type,
                'is_read'         => $m->is_read,
                'created_at'      => $m->created_at->toISOString(),
            ]);

        // Mark visitor messages as read
        if ($messages->isNotEmpty()) {
            $conversation->messages()
                ->where('sender_type', 'visitor')
                ->where('is_read', false)
                ->update(['is_read' => true, 'read_at' => now()]);
            $conversation->update(['unread_admin' => 0]);
        }

        return response()->json(['messages' => $messages]);
    }

    public function typing(Request $request, Conversation $conversation)
    {
        broadcast(new TypingIndicator($conversation->id, 'admin', $request->boolean('typing')))->toOthers();
        return response()->json(['ok' => true]);
    }

    public function close(Conversation $conversation)
    {
        $conversation->close();
        return response()->json(['status' => 'closed']);
    }

    public function reopen(Conversation $conversation)
    {
        $conversation->update(['status' => 'active', 'closed_at' => null]);
        return response()->json(['status' => 'active']);
    }

    public function destroy(Conversation $conversation)
    {
        // Delete attached files
        $conversation->messages()->whereNotNull('attachment_path')->each(function ($msg) {
            Storage::disk('public_direct')->delete($msg->attachment_path);
        });
        $conversation->delete();
        return response()->json(['deleted' => true]);
    }

    private function getAttachmentType(string $mime): string
    {
        if (str_starts_with($mime, 'image/')) return 'image';
        if (str_starts_with($mime, 'video/')) return 'video';
        if (in_array($mime, ['application/zip', 'application/x-rar-compressed'])) return 'archive';
        return 'document';
    }
}
