@extends('admin.layouts.app')
@section('title', 'Message — ' . $ticketUser->full_name)
@section('chat', '1')

@php
    $replyIcon = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>';
    $fileIcon  = '<svg class="w-5 h-5 text-brand-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>';
@endphp

@section('content')
<div class="flex h-full lg:gap-4">

    {{-- Left: threads list (desktop only) --}}
    <aside class="hidden lg:flex w-80 shrink-0 flex-col bg-white border border-slate-100 rounded-2xl overflow-hidden shadow-sm">
        <div class="flex items-center justify-between px-4 h-14 border-b border-slate-100 shrink-0">
            <span class="font-semibold text-slate-900">Messages</span>
            <a href="{{ route('admin.messages.index') }}" class="text-xs font-medium text-brand-600 hover:underline">All</a>
        </div>
        <div class="flex-1 overflow-y-auto thin-scroll">
            @foreach($threads as $thread)
            <a href="{{ route('admin.messages.show', $thread) }}" class="chat-list-item {{ $thread->id === $ticketUser->id ? 'is-active' : '' }}">
                <x-avatar :name="$thread->full_name" :image="$thread->profileImageUrl()" size-class="w-10 h-10" />
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-sm truncate {{ $thread->unread_count > 0 ? 'font-bold text-slate-900' : 'font-semibold text-slate-800' }}">{{ $thread->full_name }}</span>
                        @if($thread->unread_count > 0)
                            <span class="unread-dot">{{ $thread->unread_count > 9 ? '9+' : $thread->unread_count }}</span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 truncate">{{ $thread->last_message?->body ?: '📎 Attachment' }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </aside>

<section class="flex flex-col flex-1 min-w-0 bg-white lg:rounded-2xl lg:border lg:border-slate-100 lg:shadow-sm overflow-hidden" x-data="adminDmChat({{ $ticketUser->id }})">

    {{-- Chat header --}}
    <header class="flex items-center gap-2 sm:gap-3 px-2 sm:px-4 h-14 lg:h-16 border-b border-slate-100 bg-white shrink-0">
        <a href="{{ route('admin.messages.index') }}" class="icon-btn lg:hidden" aria-label="Back to messages">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div class="flex items-center gap-3 min-w-0 flex-1">
            <div class="relative shrink-0">
                <x-avatar :name="$ticketUser->full_name" :image="$ticketUser->profileImageUrl()" size-class="w-10 h-10" />
                @if($ticketUser->isOnline())
                    <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white"></span>
                @endif
            </div>
            <div class="min-w-0">
                <p class="font-semibold text-slate-900 text-sm truncate">{{ $ticketUser->full_name }}</p>
                <p class="text-xs text-slate-500 truncate">{{ $ticketUser->isOnline() ? 'Online now' : $ticketUser->email }}</p>
            </div>
        </div>
        <a href="{{ route('admin.ticket-users.index') }}" class="btn-secondary !px-3 !py-1.5 !text-xs hidden sm:inline-flex">Ticket Users</a>
    </header>

    {{-- Messages --}}
    <div class="flex-1 overflow-y-auto thin-scroll chat-wallpaper px-3 sm:px-5 py-4 space-y-2" id="messages-container">
        @forelse($messages as $msg)
        @php $out = $msg->sender_type === 'admin'; @endphp
        <div class="group flex items-end gap-1.5 {{ $out ? 'justify-end' : 'justify-start' }}">
            @if($out)
            <button onclick="adminMsgSetReply({{ $msg->id }}, {{ json_encode($msg->body ?: ($msg->attachment_name ?? '')) }}, '{{ $msg->sender_type }}')" class="reply-btn" title="Reply">{!! $replyIcon !!}</button>
            @endif

            <div class="min-w-0 max-w-[85%] sm:max-w-[70%] flex flex-col {{ $out ? 'items-end' : 'items-start' }}">
                @if($msg->body || $msg->replyTo)
                <div class="{{ $out ? 'bubble-out' : 'bubble-in' }}">
                    @if($msg->replyTo)
                    <span class="quote"><span class="font-semibold">{{ $msg->replyTo->sender_type === 'admin' ? 'You' : $ticketUser->full_name }}</span> · {{ $msg->replyTo->body ? Str::limit($msg->replyTo->body, 60) : ($msg->replyTo->attachment_name ?? '📎 Attachment') }}</span>
                    @endif
                    @if($msg->body)<span class="msg-text">{{ $msg->body }}</span>@endif
                </div>
                @endif

                @if($msg->attachment_url)
                    <div class="mt-1">
                        @if($msg->attachment_type === 'image')
                            <img src="{{ $msg->attachment_url }}" alt="{{ $msg->attachment_name }}"
                                class="max-w-full sm:max-w-xs rounded-2xl max-h-64 object-cover cursor-pointer shadow-sm hover:opacity-90 transition-opacity"
                                onclick="window.open('{{ $msg->attachment_url }}', '_blank')">
                        @elseif($msg->attachment_type === 'video')
                            <video src="{{ $msg->attachment_url }}" controls class="w-full sm:max-w-xs rounded-2xl max-h-64 bg-black"></video>
                        @else
                            <a href="{{ $msg->attachment_url }}" target="_blank" class="chat-file">
                                {!! $fileIcon !!}<span class="truncate text-slate-700">{{ $msg->attachment_name }}</span>
                            </a>
                        @endif
                    </div>
                @endif

                <p class="msg-time">{{ $out ? $msg->sender_name . ' · ' : '' }}{{ $msg->created_at->format('M j, H:i') }}</p>
            </div>

            @unless($out)
            <button onclick="adminMsgSetReply({{ $msg->id }}, {{ json_encode($msg->body ?: ($msg->attachment_name ?? '')) }}, '{{ $msg->sender_type }}')" class="reply-btn" title="Reply">{!! $replyIcon !!}</button>
            @endunless
        </div>
        @empty
        <div class="h-full grid place-items-center" x-show="newMessages.length === 0">
            <div class="text-center bg-white/80 rounded-2xl px-6 py-5 shadow-sm">
                <p class="text-3xl mb-1">👋</p>
                <p class="text-sm text-slate-500">No messages yet. Say hello to {{ $ticketUser->full_name }}!</p>
            </div>
        </div>
        @endforelse

        <template x-for="msg in newMessages" :key="msg.id">
            <div class="group flex items-end gap-1.5" :class="msg.sender_type === 'admin' ? 'justify-end' : 'justify-start'">
                <button x-show="msg.sender_type === 'admin'" @click="setReply(msg)" class="reply-btn" title="Reply">{!! $replyIcon !!}</button>

                <div class="min-w-0 max-w-[85%] sm:max-w-[70%] flex flex-col" :class="msg.sender_type === 'admin' ? 'items-end' : 'items-start'">
                    <div x-show="msg.body || msg.reply_to"
                         :class="msg.sender_type === 'admin' ? 'bubble-out' : 'bubble-in'"><span x-show="msg.reply_to" class="quote"><span class="font-semibold" x-text="msg.reply_to?.sender_type === 'admin' ? 'You' : @js($ticketUser->full_name)"></span> · <span x-text="msg.reply_to?.body ? msg.reply_to.body.slice(0,60) : (msg.reply_to?.attachment_name || '📎 Attachment')"></span></span><span class="msg-text" x-text="msg.body"></span></div>
                    <div x-show="msg.attachment_url && msg.attachment_type === 'image'" class="mt-1">
                        <img :src="msg.attachment_url" class="max-w-full sm:max-w-xs rounded-2xl max-h-64 object-cover cursor-pointer shadow-sm" @click="window.open(msg.attachment_url, '_blank')" />
                    </div>
                    <div x-show="msg.attachment_url && msg.attachment_type === 'video'" class="mt-1">
                        <video :src="msg.attachment_url" controls class="w-full sm:max-w-xs rounded-2xl max-h-64 bg-black"></video>
                    </div>
                    <div x-show="msg.attachment_url && msg.attachment_type !== 'image' && msg.attachment_type !== 'video'" class="mt-1">
                        <a :href="msg.attachment_url" target="_blank" class="chat-file">
                            {!! $fileIcon !!}<span class="truncate text-slate-700" x-text="msg.attachment_name || 'Attachment'"></span>
                        </a>
                    </div>
                    <p class="msg-time" x-text="(msg.sender_type === 'admin' && msg.sender_name ? msg.sender_name + ' · ' : '') + formatTime(msg.created_at)"></p>
                </div>

                <button x-show="msg.sender_type !== 'admin'" @click="setReply(msg)" class="reply-btn" title="Reply">{!! $replyIcon !!}</button>
            </div>
        </template>
    </div>

    {{-- Composer --}}
    <div class="border-t border-slate-100 bg-white px-2 sm:px-4 pt-2 pb-[max(0.5rem,env(safe-area-inset-bottom))] sm:pb-3 shrink-0">
        <div x-show="replyTo" x-cloak class="mb-2 flex items-center gap-2 pl-3 pr-2 py-2 bg-brand-50 border-l-4 border-brand-500 rounded-xl">
            <div class="flex-1 min-w-0">
                <p class="text-[11px] font-semibold text-brand-600" x-text="replyTo?.sender_type === 'admin' ? 'Replying to yourself' : 'Replying to ' + @js($ticketUser->full_name)"></p>
                <p class="text-xs text-slate-600 truncate" x-text="replyTo?.body ? replyTo.body.slice(0,80) : (replyTo?.attachment_name || '📎 Attachment')"></p>
            </div>
            <button @click="clearReply()" class="icon-btn !p-1 hover:!text-red-500" aria-label="Cancel reply">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div x-show="filePreview" x-cloak class="mb-2 p-2.5 bg-slate-50 rounded-xl border border-slate-200 flex items-center gap-2">
            <svg class="w-4 h-4 text-brand-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
            <span class="text-xs text-slate-700 truncate flex-1" x-text="filePreview"></span>
            <button @click="clearFile()" class="text-slate-400 hover:text-red-500 transition-colors cursor-pointer shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="flex items-end gap-2">
            <div class="composer flex-1 min-w-0">
                <div class="relative shrink-0 self-end">
                    <button @click="showEmoji = !showEmoji" type="button" aria-label="Emoji"
                        :class="showEmoji ? 'bg-brand-50' : 'hover:bg-slate-100'"
                        class="grid place-items-center w-9 h-9 rounded-full text-xl leading-none transition-colors cursor-pointer">😊</button>
                    <div x-show="showEmoji" x-cloak @click.outside="showEmoji = false" class="absolute z-50 bottom-[calc(100%+8px)] left-0">
                        <emoji-picker @emoji-click="insertEmoji($event.detail.unicode)" class="block w-[min(320px,calc(100vw-2rem))] h-[360px]"></emoji-picker>
                    </div>
                </div>

                <textarea x-model="message" x-ref="msgInput"
                    @keydown.enter="if (!$event.shiftKey && window.innerWidth >= 1024) { $event.preventDefault(); send(); }"
                    rows="1" placeholder="Message {{ $ticketUser->full_name }}…"
                    class="flex-1 min-w-0 resize-none bg-transparent py-2 text-[15px] sm:text-sm focus:outline-none text-slate-800 placeholder-slate-400 max-h-32 min-h-[2.25rem]"
                    oninput="this.style.height='auto';this.style.height=Math.min(this.scrollHeight,128)+'px'"></textarea>

                <input type="file" id="file-input" class="hidden" @change="handleFile($event)" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.xml,.zip,.mp4,.txt">
                <button @click="document.getElementById('file-input').click()" type="button" aria-label="Attach file"
                    class="grid place-items-center w-9 h-9 shrink-0 self-end rounded-full text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                </button>
            </div>
            <button @click="send()" :disabled="sending" type="button" class="send-btn mb-0.5" aria-label="Send">
                <svg class="w-5 h-5 rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
            </button>
        </div>
    </div>
</section>
</div>{{-- /outer flex --}}
@endsection

@push('scripts')
<script>
// Bridge so static Blade reply buttons can call Alpine's setReply
function adminMsgSetReply(id, body, senderType) {
    if (window._adminDmChatInstance) window._adminDmChatInstance.setReply({ id, body, sender_type: senderType });
}

function adminDmChat(ticketUserId) {
    return {
        message: '',
        sending: false,
        newMessages: [],
        lastMessageId: {{ $messages->last()?->id ?? 0 }},
        selectedFile: null,
        filePreview: '',
        showEmoji: false,
        replyTo: null,
        pollUrl: '{{ route('admin.messages.poll', $ticketUser) }}',
        sendUrl: '{{ route('admin.messages.message', $ticketUser) }}',

        setReply(msg) {
            this.replyTo = { id: msg.id, body: msg.body, sender_type: msg.sender_type, attachment_name: msg.attachment_name };
            this.$nextTick(() => this.$refs.msgInput?.focus());
        },

        clearReply() {
            this.replyTo = null;
        },

        insertEmoji(emoji) {
            const ta = this.$refs.msgInput;
            const start = ta.selectionStart;
            const end = ta.selectionEnd;
            this.message = this.message.slice(0, start) + emoji + this.message.slice(end);
            this.$nextTick(() => {
                ta.selectionStart = ta.selectionEnd = start + emoji.length;
                ta.focus();
            });
            this.showEmoji = false;
        },

        init() {
            window._adminDmChatInstance = this;
            this.$nextTick(() => this.scrollToBottom());
            this.startPolling();
        },

        startPolling() {
            setInterval(() => this.pollMessages(), 3000);
        },

        pollMessages() {
            fetch(`${this.pollUrl}?after_id=${this.lastMessageId}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.messages && data.messages.length) {
                    data.messages.forEach(m => {
                        if (!this.newMessages.find(n => n.id === m.id)) {
                            this.newMessages.push(m);
                            this.lastMessageId = Math.max(this.lastMessageId, m.id);
                        }
                    });
                    this.$nextTick(() => this.scrollToBottom());
                }
            })
            .catch(() => {});
        },

        send() {
            if ((!this.message.trim() && !this.selectedFile) || this.sending) return;

            this.sending = true;
            const formData = new FormData();
            if (this.message.trim()) formData.append('body', this.message.trim());
            if (this.selectedFile) formData.append('attachment', this.selectedFile);
            if (this.replyTo) formData.append('reply_to_id', this.replyTo.id);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

            const self = this;
            fetch(this.sendUrl, { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.message) {
                        self.newMessages.push({
                            id: data.message.id,
                            sender_type: 'admin',
                            sender_name: data.message.sender_name,
                            body: data.message.body,
                            attachment_url: data.attachment_url,
                            attachment_name: data.message.attachment_name,
                            attachment_type: data.message.attachment_type,
                            created_at: data.message.created_at,
                            reply_to: self.replyTo ? { id: self.replyTo.id, body: self.replyTo.body, sender_type: self.replyTo.sender_type, attachment_name: self.replyTo.attachment_name } : null,
                        });
                        self.lastMessageId = Math.max(self.lastMessageId, data.message.id);
                        self.$nextTick(() => self.scrollToBottom());
                    }
                    self.sending = false;
                    self.message = '';
                    self.clearReply();
                    self.clearFile();
                })
                .catch(() => { self.sending = false; });
        },

        handleFile(event) {
            const file = event.target.files[0];
            if (!file) return;
            this.selectedFile = file;
            this.filePreview = file.name;
        },

        clearFile() {
            this.selectedFile = null;
            this.filePreview = '';
            document.getElementById('file-input').value = '';
        },

        scrollToBottom() {
            const container = document.getElementById('messages-container');
            if (container) container.scrollTop = container.scrollHeight;
        },

        formatTime(iso) {
            const d = new Date(iso);
            return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }
    }
}
</script>
@endpush
