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

        $messages = DirectMessage::between($user->id, $contact->id)->orderBy('created_at')->get();

        DirectMessage::where('sender_id', $contact->id)
            ->where('recipient_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $groups = $this->sidebarGroups($user);
        $contacts = $this->sidebarContacts($user);

        return view('tickets.chat.dm', compact('contact', 'messages', 'groups', 'contacts'));
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
            'sender_id'    => $user->id,
            'recipient_id' => $contact->id,
            'body'         => $request->body,
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

        $messages = DirectMessage::between($user->id, $contact->id)
            ->when($afterId, fn($q) => $q->where('id', '>', $afterId))
            ->orderBy('created_at')
            ->get()
            ->map(fn($m) => [
                'id'              => $m->id,
                'sender_id'       => $m->sender_id,
                'is_mine'         => $m->sender_id === $user->id,
                'body'            => $m->body,
                'attachment_url'  => $m->attachment_url,
                'attachment_name' => $m->attachment_name,
                'attachment_type' => $m->attachment_type,
                'created_at'      => $m->created_at->toISOString(),
            ]);

        if ($messages->isNotEmpty()) {
            DirectMessage::where('sender_id', $contact->id)
                ->where('recipient_id', $user->id)
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
