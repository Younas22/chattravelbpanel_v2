<?php

namespace App\Http\Controllers\Chat;

use App\Events\MessageSent;
use App\Events\NewConversation;
use App\Events\TypingIndicator;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\QuickFaq;
use App\Models\Visitor;
use App\Models\WidgetSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class ConversationController extends Controller
{
    public function faqs()
    {
        $faqs = QuickFaq::active()->get(['id', 'question', 'answer', 'show_chat_button']);
        return response()->json($faqs);
    }

    public function settings()
    {
        $defaults = WidgetSetting::defaults();
        $saved    = WidgetSetting::getAll();
        $settings = array_merge($defaults, $saved);

        // Only expose safe settings to widget
        return response()->json([
            'primary_color'      => $settings['primary_color'],
            'text_color'         => $settings['text_color'],
            'position'           => $settings['position'],
            'border_radius'      => $settings['border_radius'],
            'dark_mode'          => $settings['dark_mode'],
            'welcome_message'    => $settings['welcome_message'],
            'offline_message'    => $settings['offline_message'],
            'widget_title'       => $settings['widget_title'],
            'widget_subtitle'    => $settings['widget_subtitle'],
            'auto_popup'         => $settings['auto_popup'],
            'popup_delay'        => $settings['popup_delay'],
            'sound_enabled'      => $settings['sound_enabled'],
            'show_online_status' => $settings['show_online_status'],
            'agent_name'         => $settings['agent_name'],
            'show_branding'      => $settings['show_branding'],
            'company_image'      => $settings['company_image'] ? url($settings['company_image']) : '',
        ]);
    }

    public function start(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string|max:64',
            'name'       => 'nullable|string|max:100',
            'email'      => 'nullable|email|max:200',
        ]);

        $key = 'start_conversation_' . $request->session_id;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json(['error' => 'Too many requests. Please wait.'], 429);
        }
        RateLimiter::hit($key, 60);

        $visitor = Visitor::where('session_id', $request->session_id)->first();
        if (!$visitor) {
            return response()->json(['error' => 'Visitor not found.'], 404);
        }

        // Update visitor info if provided
        if ($request->name || $request->email) {
            $visitor->update(array_filter([
                'name'  => $request->name,
                'email' => $request->email,
            ]));
        }

        // Check for existing open conversation
        $conversation = $visitor->activeConversation;

        if (!$conversation) {
            $conversation = Conversation::create([
                'visitor_id' => $visitor->id,
                'status'     => 'pending',
            ]);

            broadcast(new NewConversation($conversation));
        }

        return response()->json([
            'conversation_id' => $conversation->id,
            'status'          => $conversation->status,
        ]);
    }

    public function messages(Request $request, int $conversationId)
    {
        $sessionId = $request->header('X-Visitor-Session') ?? $request->session_id;
        $visitor   = Visitor::where('session_id', $sessionId)->first();

        if (!$visitor) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $conversation = Conversation::where('id', $conversationId)
            ->where('visitor_id', $visitor->id)
            ->firstOrFail();

        $afterId = $request->integer('after_id', 0);

        $messages = $conversation->messages()
            ->with('replyTo')
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
                'reply_to'        => $m->replyTo ? [
                    'id'          => $m->replyTo->id,
                    'body'        => $m->replyTo->body,
                    'sender_type' => $m->replyTo->sender_type,
                    'attachment_name' => $m->replyTo->attachment_name,
                ] : null,
            ]);

        // Mark admin messages as read
        $conversation->messages()
            ->where('sender_type', 'admin')
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        $conversation->update(['unread_visitor' => 0]);

        return response()->json(['messages' => $messages]);
    }

    public function send(Request $request, int $conversationId)
    {
        $sessionId = $request->header('X-Visitor-Session') ?? $request->session_id;
        $visitor   = Visitor::where('session_id', $sessionId)->first();

        if (!$visitor) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $key = 'send_message_' . $visitor->id;
        if (RateLimiter::tooManyAttempts($key, 30)) {
            return response()->json(['error' => 'Too many messages. Slow down.'], 429);
        }
        RateLimiter::hit($key, 60);

        $conversation = Conversation::where('id', $conversationId)
            ->where('visitor_id', $visitor->id)
            ->firstOrFail();

        $request->validate([
            'body'       => 'nullable|string|max:5000',
            'attachment' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf,xml,zip,mp4,txt',
        ]);

        if (!$request->body && !$request->hasFile('attachment')) {
            return response()->json(['error' => 'Message or attachment required.'], 422);
        }

        $data = [
            'conversation_id' => $conversation->id,
            'sender_type'     => 'visitor',
            'body'            => $request->body,
            'reply_to_id'     => $request->integer('reply_to_id') ?: null,
        ];

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store((string) $conversation->id, 'public_direct');
            $data['attachment_path'] = $path;
            $data['attachment_name'] = $file->getClientOriginalName();
            $data['attachment_mime'] = $file->getMimeType();
            $data['attachment_size'] = $file->getSize();
            $data['attachment_type'] = str_starts_with($file->getMimeType(), 'image/') ? 'image'
                : (str_starts_with($file->getMimeType(), 'video/') ? 'video' : 'document');
        }

        $message = Message::create($data);

        if ($conversation->status === 'pending') {
            $conversation->update(['status' => 'active']);
        }

        $conversation->increment('unread_admin');
        $conversation->touch();

        broadcast(new MessageSent($message))->toOthers();

        $replyTo = $message->reply_to_id ? $message->replyTo : null;

        return response()->json([
            'id'             => $message->id,
            'body'           => $message->body,
            'attachment_url' => $message->attachment_url,
            'created_at'     => $message->created_at->toISOString(),
            'reply_to'       => $replyTo ? [
                'id'          => $replyTo->id,
                'body'        => $replyTo->body,
                'sender_type' => $replyTo->sender_type,
                'attachment_name' => $replyTo->attachment_name,
            ] : null,
        ]);
    }

    public function typing(Request $request, int $conversationId)
    {
        $sessionId = $request->header('X-Visitor-Session') ?? $request->session_id;
        $visitor   = Visitor::where('session_id', $sessionId)->first();

        if ($visitor) {
            broadcast(new TypingIndicator($conversationId, 'visitor', $request->boolean('typing')))->toOthers();
        }

        return response()->json(['ok' => true]);
    }
}
