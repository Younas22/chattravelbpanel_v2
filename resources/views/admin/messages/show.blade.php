@extends('admin.layouts.app')
@section('title', 'Message — ' . $ticketUser->full_name)

@section('content')
<div class="flex h-[calc(100vh-8rem)] relative gap-0">

    {{-- Left: Threads List --}}
    <div class="w-72 shrink-0 flex flex-col bg-white border border-slate-100 rounded-2xl overflow-hidden mr-4">
        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 shrink-0">
            <span class="font-semibold text-slate-900 text-sm">Messages</span>
            <a href="{{ route('admin.messages.index') }}" class="text-xs text-blue-600 hover:underline">All</a>
        </div>
        <div class="flex-1 overflow-y-auto divide-y divide-slate-50">
            @foreach($threads as $thread)
            <a href="{{ route('admin.messages.show', $thread) }}"
               class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 transition-colors {{ $thread->id === $ticketUser->id ? 'bg-blue-50 border-l-2 border-blue-500' : '' }}">
                <x-avatar :name="$thread->full_name" :image="$thread->profileImageUrl()" size-class="w-9 h-9" />
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-1">
                        <span class="text-sm font-semibold text-slate-900 truncate">{{ $thread->full_name }}</span>
                        @if($thread->unread_count > 0)
                            <span class="shrink-0 w-4 h-4 bg-blue-600 text-white text-[9px] rounded-full flex items-center justify-center font-bold">{{ $thread->unread_count > 9 ? '9+' : $thread->unread_count }}</span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 truncate">{{ $thread->last_message?->body ?: '📎 Attachment' }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>

<div class="flex flex-1" x-data="adminDmChat({{ $ticketUser->id }})">

    {{-- Chat Panel --}}
    <div class="flex flex-col flex-1 card !p-0 overflow-hidden">

        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <x-avatar :name="$ticketUser->full_name" :image="$ticketUser->profileImageUrl()" size-class="w-10 h-10" />
                <div>
                    <p class="font-semibold text-slate-900 text-sm">{{ $ticketUser->full_name }}</p>
                    <p class="text-xs text-slate-500">{{ $ticketUser->email }}</p>
                </div>
            </div>
            <a href="{{ route('admin.ticket-users.index') }}" class="btn-secondary !py-1.5 !text-xs">← Ticket Users</a>
        </div>

        <div class="flex-1 overflow-y-auto p-5 space-y-3" id="messages-container">
            @forelse($messages as $msg)
            <div class="group flex items-start gap-2 {{ $msg->sender_type === 'admin' ? 'justify-start' : 'justify-end' }}">
                @if($msg->sender_type !== 'admin')
                <button onclick="adminMsgSetReply({{ $msg->id }}, {{ json_encode($msg->body ?: ($msg->attachment_name ?? '')) }}, '{{ $msg->sender_type }}')"
                    class="opacity-0 group-hover:opacity-100 transition-opacity mt-5 p-1 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 cursor-pointer shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                </button>
                @endif
                <div class="max-w-[70%]">
                    <p class="text-[11px] font-medium text-slate-400 mb-1 {{ $msg->sender_type === 'admin' ? '' : 'text-right' }}">{{ $msg->sender_type === 'admin' ? $msg->sender_name : $ticketUser->full_name }}</p>

                    @if($msg->replyTo)
                    <div class="mb-1 px-3 py-1.5 rounded-xl border-l-4 {{ $msg->sender_type === 'admin' ? 'border-white/50 bg-blue-500/30' : 'border-blue-400 bg-slate-100' }} text-xs text-slate-500 truncate">
                        <span class="font-semibold {{ $msg->sender_type === 'admin' ? 'text-white/80' : 'text-blue-600' }}">
                            {{ $msg->replyTo->sender_type === 'admin' ? 'You' : $ticketUser->full_name }}
                        </span>
                        <span class="{{ $msg->sender_type === 'admin' ? 'text-white/70' : 'text-slate-500' }}">
                            {{ $msg->replyTo->body ? Str::limit($msg->replyTo->body, 60) : ($msg->replyTo->attachment_name ?? '📎 Attachment') }}
                        </span>
                    </div>
                    @endif

                    @if($msg->body)
                        <div class="px-4 py-2.5 rounded-2xl text-sm break-words {{ $msg->sender_type === 'admin' ? 'bg-blue-600 text-white rounded-bl-sm' : 'bg-slate-100 text-slate-900 rounded-br-sm' }}" style="overflow-wrap:break-word;word-break:break-word;">
                            {!! nl2br(e($msg->body)) !!}
                        </div>
                    @endif

                    @if($msg->attachment_url)
                        <div class="mt-1.5">
                            @if($msg->attachment_type === 'image')
                                <img src="{{ $msg->attachment_url }}" alt="{{ $msg->attachment_name }}"
                                    class="max-w-full rounded-xl max-h-60 object-cover cursor-pointer hover:opacity-90 transition-opacity"
                                    onclick="window.open('{{ $msg->attachment_url }}', '_blank')">
                            @elseif($msg->attachment_type === 'video')
                                <video src="{{ $msg->attachment_url }}" controls class="max-w-full rounded-xl max-h-60 bg-black" style="max-width:320px;"></video>
                            @else
                                <a href="{{ $msg->attachment_url }}" target="_blank"
                                    class="flex items-center gap-2 px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors text-sm">
                                    <svg class="w-5 h-5 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                    <span class="truncate text-slate-700">{{ $msg->attachment_name }}</span>
                                </a>
                            @endif
                        </div>
                    @endif

                    <p class="text-[10px] text-slate-400 mt-1 {{ $msg->sender_type === 'admin' ? '' : 'text-right' }}">{{ $msg->created_at->format('M j, H:i') }}</p>
                </div>
                @if($msg->sender_type === 'admin')
                <button onclick="adminMsgSetReply({{ $msg->id }}, {{ json_encode($msg->body ?: ($msg->attachment_name ?? '')) }}, '{{ $msg->sender_type }}')"
                    class="opacity-0 group-hover:opacity-100 transition-opacity mt-5 p-1 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 cursor-pointer shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                </button>
                @endif
            </div>
            @empty
            <div class="py-10 text-center text-slate-400 text-sm">No messages yet. Say hello to {{ $ticketUser->full_name }}!</div>
            @endforelse

            <template x-for="msg in newMessages" :key="msg.id">
                <div class="group flex items-start gap-2" :class="msg.sender_type === 'admin' ? 'justify-start' : 'justify-end'">
                    <template x-if="msg.sender_type !== 'admin'">
                        <button @click="setReply(msg)"
                            class="opacity-0 group-hover:opacity-100 transition-opacity mt-5 p-1 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 cursor-pointer shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                        </button>
                    </template>
                    <div class="max-w-[70%]">
                        <p class="text-[11px] font-medium text-slate-400 mb-1" :class="msg.sender_type === 'admin' ? '' : 'text-right'" x-text="msg.sender_name"></p>
                        <div x-show="msg.reply_to" class="mb-1 px-3 py-1.5 rounded-xl border-l-4 text-xs truncate"
                             :class="msg.sender_type === 'admin' ? 'border-white/50 bg-blue-500/30 text-white/70' : 'border-blue-400 bg-slate-100 text-slate-500'">
                            <span class="font-semibold" x-text="msg.reply_to?.sender_type === 'admin' ? 'You' : '{{ $ticketUser->full_name }}'"></span>
                            <span x-text="msg.reply_to?.body ? msg.reply_to.body.slice(0,60) : (msg.reply_to?.attachment_name || '📎 Attachment')"></span>
                        </div>
                        <div x-show="msg.body"
                            :class="msg.sender_type === 'admin' ? 'bg-blue-600 text-white rounded-2xl rounded-bl-sm px-4 py-2.5 text-sm' : 'bg-slate-100 text-slate-900 rounded-2xl rounded-br-sm px-4 py-2.5 text-sm'"
                            style="overflow-wrap:break-word;word-break:break-word;"
                            x-text="msg.body"></div>
                        <div x-show="msg.attachment_url && msg.attachment_type === 'image'" class="mt-1.5">
                            <img :src="msg.attachment_url" class="max-w-full rounded-xl max-h-60 object-cover cursor-pointer" @click="window.open(msg.attachment_url, '_blank')" />
                        </div>
                        <div x-show="msg.attachment_url && msg.attachment_type === 'video'" class="mt-1.5">
                            <video :src="msg.attachment_url" controls class="max-w-full rounded-xl max-h-60 bg-black" style="max-width:320px;"></video>
                        </div>
                        <div x-show="msg.attachment_url && msg.attachment_type !== 'image' && msg.attachment_type !== 'video'" class="mt-1.5">
                            <a :href="msg.attachment_url" target="_blank" class="flex items-center gap-2 px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors text-sm">
                                <svg class="w-5 h-5 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 002 2v14a2 2 0 002 2z"/></svg>
                                <span class="truncate text-slate-700" x-text="msg.attachment_name || 'Attachment'"></span>
                            </a>
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1" :class="msg.sender_type === 'admin' ? '' : 'text-right'" x-text="formatTime(msg.created_at)"></p>
                    </div>
                    <template x-if="msg.sender_type === 'admin'">
                        <button @click="setReply(msg)"
                            class="opacity-0 group-hover:opacity-100 transition-opacity mt-5 p-1 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 cursor-pointer shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                        </button>
                    </template>
                </div>
            </template>
        </div>

        <div class="border-t border-slate-100 px-4 pt-3 pb-4">
            {{-- Reply-to preview --}}
            <div x-show="replyTo" x-cloak class="mb-2 flex items-center gap-2 pl-3 pr-2 py-2 bg-blue-50 border-l-4 border-blue-500 rounded-xl">
                <div class="flex-1 min-w-0">
                    <p class="text-[11px] font-semibold text-blue-600" x-text="replyTo?.sender_type === 'admin' ? 'Replying to yourself' : 'Replying to {{ $ticketUser->full_name }}'"></p>
                    <p class="text-xs text-slate-600 truncate" x-text="replyTo?.body ? replyTo.body.slice(0,80) : (replyTo?.attachment_name || '📎 Attachment')"></p>
                </div>
                <button @click="clearReply()" class="text-slate-400 hover:text-red-500 transition-colors cursor-pointer shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div x-show="filePreview" x-cloak class="mb-2 p-2.5 bg-slate-50 rounded-xl border border-slate-200 flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                <span class="text-xs text-slate-700 truncate flex-1" x-text="filePreview"></span>
                <button @click="clearFile()" class="text-slate-400 hover:text-red-500 transition-colors cursor-pointer shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="flex items-end gap-2 rounded-2xl border border-slate-200 bg-white px-3 py-2 focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-blue-500 transition-all">
                <textarea x-model="message" x-ref="msgInput"
                    @keydown.enter.prevent="if(!$event.shiftKey) send()"
                    rows="1" placeholder="Message {{ $ticketUser->full_name }}…"
                    class="flex-1 resize-none bg-transparent text-sm focus:outline-none text-slate-800 placeholder-slate-400"
                    style="max-height:120px;min-height:24px;"
                    oninput="this.style.height='auto';this.style.height=Math.min(this.scrollHeight,120)+'px'"></textarea>

                <div class="flex items-center gap-0.5 shrink-0 pb-0.5">
                    <div class="relative">
                        <button @click="showEmoji = !showEmoji" type="button"
                            :class="showEmoji ? 'bg-blue-50 text-blue-600' : 'text-slate-400 hover:text-slate-600 hover:bg-slate-100'"
                            class="p-1.5 rounded-lg transition-colors text-lg leading-none cursor-pointer">😊</button>
                        <div x-show="showEmoji" x-cloak @click.outside="showEmoji = false"
                            class="absolute z-50" style="bottom:calc(100% + 6px);right:0;">
                            <emoji-picker @emoji-click="insertEmoji($event.detail.unicode)" style="width:320px;height:380px;display:block;"></emoji-picker>
                        </div>
                    </div>
                    <input type="file" id="file-input" class="hidden" @change="handleFile($event)"
                        accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.xml,.zip,.mp4,.txt">
                    <button @click="document.getElementById('file-input').click()" type="button"
                        class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                    </button>
                    <button @click="send()" :disabled="sending" type="button"
                        class="ml-1 p-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white transition-colors disabled:opacity-50 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>{{-- /adminDmChat --}}
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
