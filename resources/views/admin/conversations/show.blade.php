@extends('admin.layouts.app')
@section('title', 'Chat — ' . $conversation->visitor->display_name)

@section('content')
<div class="flex gap-6 h-[calc(100vh-8rem)] relative" x-data="adminChat({{ $conversation->id }})">

    {{-- Chat Panel --}}
    <div class="flex flex-col flex-1 card !p-0 overflow-hidden">

        {{-- Chat Header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <div class="w-10 h-10 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center font-semibold">
                        {{ strtoupper(substr($conversation->visitor->display_name, 0, 1)) }}
                    </div>
                    @if($conversation->visitor->is_online)
                        <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white"></span>
                    @endif
                </div>
                <div>
                    <p class="font-semibold text-slate-900 text-sm">{{ $conversation->visitor->display_name }}</p>
                    <p class="text-xs text-slate-500">
                        {{ $conversation->visitor->is_online ? 'Online now' : 'Last seen ' . optional($conversation->visitor->last_activity_at)->diffForHumans() }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="badge {{ $conversation->status === 'active' ? 'bg-green-100 text-green-700' : ($conversation->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-slate-100 text-slate-500') }}">
                    {{ ucfirst($conversation->status) }}
                </span>

                @if($conversation->status !== 'closed')
                    <button @click="closeChat()" class="btn-secondary !py-1.5 !text-xs">Close Chat</button>
                @else
                    <button @click="reopenChat()" class="btn-secondary !py-1.5 !text-xs">Reopen</button>
                @endif

                <a href="{{ route('admin.conversations.index') }}" class="btn-secondary !py-1.5 !text-xs">← Back</a>
            </div>
        </div>

        {{-- Messages --}}
        <div class="flex-1 overflow-y-auto p-5 space-y-4" id="messages-container">
            @foreach($conversation->messages as $msg)
            <div class="flex {{ $msg->sender_type === 'admin' ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-[70%]">
                    @if($msg->body)
                        <div class="px-4 py-2.5 rounded-2xl text-sm break-words {{ $msg->sender_type === 'admin' ? 'bg-blue-600 text-white rounded-br-sm' : ($msg->sender_type === 'bot' ? 'bg-slate-100 text-slate-700 rounded-bl-sm border border-slate-200' : 'bg-slate-100 text-slate-900 rounded-bl-sm') }}" style="overflow-wrap:break-word;word-break:break-word;">
                            {!! nl2br(e($msg->body)) !!}
                        </div>
                    @endif

                    @if($msg->attachment_url)
                        <div class="mt-1.5">
                            @if($msg->attachment_type === 'image')
                                <img src="{{ $msg->attachment_url }}" alt="{{ $msg->attachment_name }}"
                                    class="max-w-full rounded-xl max-h-60 object-cover cursor-pointer hover:opacity-90 transition-opacity"
                                    onclick="window.open('{{ $msg->attachment_url }}', '_blank')">
                            @else
                                <a href="{{ $msg->attachment_url }}" target="_blank"
                                    class="flex items-center gap-2 px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors text-sm">
                                    <svg class="w-5 h-5 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                    <span class="truncate text-slate-700">{{ $msg->attachment_name }}</span>
                                </a>
                            @endif
                        </div>
                    @endif

                    <div class="flex items-center gap-1 mt-1 {{ $msg->sender_type === 'admin' ? 'justify-end' : 'justify-start' }}">
                        <span class="text-[10px] text-slate-400">{{ $msg->created_at->format('H:i') }}</span>
                        @if($msg->sender_type === 'admin' && $msg->is_read)
                            <svg class="w-3 h-3 text-blue-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.707 14.293l-3-3a1 1 0 00-1.414 1.414l3.5 3.5a1 1 0 001.414 0l7-7a1 1 0 00-1.414-1.414L9.707 14.293z"/></svg>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach

            {{-- Typing indicator --}}
            <div x-show="visitorTyping" x-transition class="flex justify-start">
                <div class="bg-slate-100 rounded-2xl rounded-bl-sm px-4 py-3">
                    <div class="flex gap-1">
                        <span class="w-2 h-2 bg-slate-400 rounded-full animate-bounce" style="animation-delay:0ms"></span>
                        <span class="w-2 h-2 bg-slate-400 rounded-full animate-bounce" style="animation-delay:150ms"></span>
                        <span class="w-2 h-2 bg-slate-400 rounded-full animate-bounce" style="animation-delay:300ms"></span>
                    </div>
                </div>
            </div>

            {{-- New messages from polling --}}
            <template x-for="msg in newMessages" :key="msg.id">
                <div :class="msg.sender_type === 'admin' ? 'flex justify-end' : 'flex justify-start'">
                    <div class="max-w-[70%]">
                        <div x-show="msg.body"
                            :class="msg.sender_type === 'admin' ? 'bg-blue-600 text-white rounded-2xl rounded-br-sm px-4 py-2.5 text-sm' : 'bg-slate-100 text-slate-900 rounded-2xl rounded-bl-sm px-4 py-2.5 text-sm'"
                            style="overflow-wrap:break-word;word-break:break-word;"
                            x-text="msg.body"></div>
                        <div x-show="msg.attachment_url && msg.attachment_type === 'image'" class="mt-1.5">
                            <img :src="msg.attachment_url" class="max-w-full rounded-xl max-h-60 object-cover cursor-pointer"
                                @click="window.open(msg.attachment_url, '_blank')" />
                        </div>
                        <div x-show="msg.attachment_url && msg.attachment_type !== 'image'" class="mt-1.5">
                            <a :href="msg.attachment_url" target="_blank"
                                class="flex items-center gap-2 px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors text-sm">
                                <svg class="w-5 h-5 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                <span class="truncate text-slate-700" x-text="msg.attachment_name || 'Attachment'"></span>
                            </a>
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1" :class="msg.sender_type === 'admin' ? 'text-right' : ''" x-text="formatTime(msg.created_at)"></p>
                    </div>
                </div>
            </template>
        </div>

        {{-- Typing bar --}}
        <div class="border-t border-slate-100 px-4 pt-3 pb-4 relative">

            {{-- Canned replies search --}}
            <div x-show="showCanned" x-cloak class="mb-3 border border-slate-200 rounded-xl overflow-hidden shadow-lg max-h-48 overflow-y-auto">
                <div class="p-2 border-b border-slate-100">
                    <input type="text" x-model="cannedSearch" @input="searchCanned()" placeholder="Search replies…"
                        class="w-full text-sm px-3 py-1.5 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <template x-for="reply in cannedResults" :key="reply.id">
                    <button @click="useCanned(reply)" class="w-full text-left px-4 py-2.5 hover:bg-blue-50 transition-colors border-b border-slate-50 last:border-0">
                        <p class="text-sm font-medium text-slate-800" x-text="reply.title"></p>
                        <p class="text-xs text-slate-500 truncate" x-text="reply.body"></p>
                    </button>
                </template>
                <div x-show="cannedResults.length === 0" class="px-4 py-3 text-sm text-slate-400 text-center">No replies found</div>
            </div>

            {{-- File preview --}}
            <div x-show="filePreview" x-cloak class="mb-2 flex items-center gap-2 p-2.5 bg-slate-50 rounded-xl border border-slate-200">
                <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                <span class="text-xs text-slate-700 truncate flex-1" x-text="filePreview"></span>
                <button @click="clearFile()" class="text-slate-400 hover:text-red-500 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Input row --}}
            <div class="flex items-end gap-2 rounded-2xl border border-slate-200 bg-white px-3 py-2 focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-blue-500 transition-all">
                <textarea x-model="message" x-ref="msgInput"
                    @keydown.enter.prevent="if(!$event.shiftKey) send()"
                    @input="handleTyping()" @keyup.slash="showCannedMenu()"
                    rows="1" placeholder="Type a message… (/ for canned replies)"
                    class="flex-1 resize-none bg-transparent text-sm focus:outline-none text-slate-800 placeholder-slate-400"
                    style="max-height:120px;min-height:24px;"
                    oninput="this.style.height='auto';this.style.height=Math.min(this.scrollHeight,120)+'px'"></textarea>

                <div class="flex items-center gap-0.5 shrink-0 pb-0.5">
                    <input type="file" id="file-input" class="hidden" @change="handleFile($event)"
                        accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.xml,.zip,.mp4,.txt">

                    {{-- Emoji button --}}
                    <button @click="showEmoji=!showEmoji" type="button"
                        :class="showEmoji ? 'bg-blue-50 text-blue-600' : 'text-slate-400 hover:text-slate-600 hover:bg-slate-100'"
                        class="p-1.5 rounded-lg transition-colors text-lg leading-none">
                        😊
                    </button>

                    <button @click="document.getElementById('file-input').click()" type="button"
                        class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                    </button>

                    <button @click="toggleCanned()" type="button"
                        :class="showCanned ? 'bg-blue-50 text-blue-600' : 'text-slate-400 hover:text-slate-600 hover:bg-slate-100'"
                        class="p-1.5 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h7"/></svg>
                    </button>

                    <button @click="send()" :disabled="sending" type="button"
                        class="ml-1 p-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white transition-colors disabled:opacity-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Visitor Details Sidebar --}}
    <div class="w-72 shrink-0 space-y-4 overflow-y-auto">

        <div class="card">
            <h3 class="font-semibold text-slate-900 text-sm mb-3">Visitor Info</h3>
            <div class="space-y-2.5 text-sm">
                @foreach([
                    ['IP', $conversation->visitor->ip_address],
                    ['Country', $conversation->visitor->country],
                    ['City', $conversation->visitor->city],
                    ['Browser', $conversation->visitor->browser],
                    ['OS', $conversation->visitor->os],
                    ['Device', ucfirst($conversation->visitor->device)],
                ] as [$label, $value])
                    @if($value)
                    <div class="flex justify-between">
                        <span class="text-slate-500">{{ $label }}</span>
                        <span class="font-medium text-slate-800 text-right">{{ $value }}</span>
                    </div>
                    @endif
                @endforeach

                @if($conversation->visitor->name)
                <div class="flex justify-between">
                    <span class="text-slate-500">Name</span>
                    <span class="font-medium text-slate-800">{{ $conversation->visitor->name }}</span>
                </div>
                @endif

                @if($conversation->visitor->email)
                <div class="flex justify-between">
                    <span class="text-slate-500">Email</span>
                    <span class="font-medium text-slate-800 break-all text-right">{{ $conversation->visitor->email }}</span>
                </div>
                @endif
            </div>
        </div>

        <div class="card">
            <h3 class="font-semibold text-slate-900 text-sm mb-3">Session</h3>
            <div class="space-y-2 text-sm">
                <div>
                    <span class="text-slate-500 block">Landing Page</span>
                    <span class="text-xs text-slate-700 break-all">{{ $conversation->visitor->landing_page }}</span>
                </div>
                <div>
                    <span class="text-slate-500 block">Current Page</span>
                    <span class="text-xs text-slate-700 break-all">{{ $conversation->visitor->current_page }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Started</span>
                    <span class="text-slate-800">{{ $conversation->created_at->format('M j, H:i') }}</span>
                </div>
            </div>
        </div>

        @if($conversation->visitor->logs->isNotEmpty())
        <div class="card">
            <h3 class="font-semibold text-slate-900 text-sm mb-3">Page History</h3>
            <div class="space-y-1.5">
                @foreach($conversation->visitor->logs->take(8) as $log)
                <div class="text-xs">
                    <p class="text-slate-700 truncate">{{ $log->page_title ?: $log->page_url }}</p>
                    <p class="text-slate-400">{{ $log->visited_at->format('H:i') }}</p>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Emoji Panel — outside overflow-hidden card so it's not clipped --}}
    <div x-show="showEmoji" x-cloak @click.outside="showEmoji=false"
        class="absolute z-50 border border-slate-200 rounded-xl shadow-xl bg-white"
        style="bottom:76px;left:16px;width:300px;">
        <div class="flex gap-1 p-2 border-b border-slate-100">
            <template x-for="cat in emojiCats" :key="cat.key">
                <button @click="emojiCategory=cat.key" type="button"
                    :class="emojiCategory===cat.key ? 'bg-blue-100 text-blue-700' : 'hover:bg-slate-100 text-slate-500'"
                    class="px-2 py-1 rounded-lg text-base transition-colors" x-text="cat.icon"></button>
            </template>
        </div>
        <div class="p-2 overflow-y-auto" style="display:grid;grid-template-columns:repeat(9,1fr);gap:2px;max-height:150px;">
            <template x-for="e in emojiList()" :key="e">
                <button @click="insertEmoji(e)" type="button"
                    class="text-lg p-1 rounded hover:bg-slate-100 transition-colors leading-none" x-text="e"></button>
            </template>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function adminChat(conversationId) {
    return {
        message: '',
        sending: false,
        newMessages: [],
        lastMessageId: {{ $conversation->messages->last()?->id ?? 0 }},
        visitorTyping: false,
        typingTimer: null,
        sendingTyping: false,
        showCanned: false,
        cannedSearch: '',
        cannedResults: [],
        fileInput: null,
        selectedFile: null,
        filePreview: '',
        showEmoji: false,
        emojiCategory: 'smileys',
        emojiCats: [
            {key:'smileys', icon:'😊'}, {key:'gestures', icon:'👍'},
            {key:'travel', icon:'✈️'}, {key:'objects', icon:'💼'}, {key:'symbols', icon:'❤️'}
        ],
        emojiData: {
            smileys:  ['😀','😃','😄','😁','😆','😅','😂','🤣','😊','😇','🙂','🙃','😉','😌','😍','🥰','😘','😗','😙','😚','😋','😛','😝','😜','🤪','🤨','🧐','🤓','😎','🥸','🤩','🥳','😏','😒','😞','😔','😟','😕','🙁','☹️','😣','😖','😫','😩','🥺','😢','😭','😤','😠','😡'],
            gestures: ['👍','👎','👌','🤌','🤏','✌️','🤞','🤟','🤘','🤙','👈','👉','👆','👇','☝️','👋','🤚','🖐️','✋','🖖','🤝','🙏','✍️','💪','👀','👅','👄','🫶','🤲','👐'],
            travel:   ['✈️','🚀','🛸','🚁','🛶','⛵','🚢','🚂','🚄','🚗','🚕','🚌','🚎','🏖️','🏔️','🗺️','🧭','🏕️','🌍','🌎','🌏','🗼','🗽','🏰','🏯','🎡','🎢','🎠','⛽','🚦'],
            objects:  ['💼','💻','📱','⌨️','🖥️','📷','📹','🎥','📞','☎️','📺','📻','⏱️','⌚','📦','📫','✏️','📝','📁','📂','📅','💡','🔦','🔋','🔌','🛠️','🔧','🔨','🗝️','🔐'],
            symbols:  ['❤️','🧡','💛','💚','💙','💜','🖤','🤍','💔','❣️','💕','💞','💓','💗','💖','💘','💝','☮️','✝️','☪️','✡️','☯️','⭐','🌟','💫','✨','🔥','💥','🎉','🎊'],
        },

        init() {
            this.$nextTick(() => this.scrollToBottom());
            this.startPolling();
            this.loadCannedReplies();
        },

        startPolling() {
            setInterval(() => this.pollMessages(), 3000);
        },

        pollMessages() {
            fetch(`/admin/conversations/${conversationId}/messages?after_id=${this.lastMessageId}`, {
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
                    if (data.messages.some(m => m.sender_type === 'visitor')) {
                        this.playSound();
                    }
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
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

            fetch(`/admin/conversations/${conversationId}/message`, {
                method: 'POST',
                body: formData,
            })
            .then(r => r.json())
            .then(data => {
                if (data.message) {
                    this.newMessages.push({
                        id: data.message.id,
                        sender_type: 'admin',
                        body: data.message.body,
                        attachment_url: data.attachment_url,
                        attachment_type: data.message.attachment_type,
                        created_at: data.message.created_at,
                    });
                    this.lastMessageId = Math.max(this.lastMessageId, data.message.id);
                    this.$nextTick(() => this.scrollToBottom());
                }
                this.message = '';
                this.clearFile();
            })
            .finally(() => { this.sending = false; });
        },

        handleTyping() {
            if (!this.sendingTyping) {
                this.sendingTyping = true;
                fetch(`/admin/conversations/${conversationId}/typing`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ typing: true }),
                }).finally(() => { this.sendingTyping = false; });
            }
            clearTimeout(this.typingTimer);
            this.typingTimer = setTimeout(() => {
                fetch(`/admin/conversations/${conversationId}/typing`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ typing: false }),
                });
            }, 2000);
        },

        closeChat() {
            if (!confirm('Close this conversation?')) return;
            fetch(`/admin/conversations/${conversationId}/close`, {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            }).then(() => location.reload());
        },

        reopenChat() {
            fetch(`/admin/conversations/${conversationId}/reopen`, {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            }).then(() => location.reload());
        },

        loadCannedReplies() {
            fetch('/admin/canned-replies/search')
                .then(r => r.json())
                .then(data => { this.cannedResults = data; });
        },

        searchCanned() {
            fetch(`/admin/canned-replies/search?q=${encodeURIComponent(this.cannedSearch)}`)
                .then(r => r.json())
                .then(data => { this.cannedResults = data; });
        },

        toggleCanned() {
            this.showCanned = !this.showCanned;
            if (this.showCanned) this.loadCannedReplies();
        },

        showCannedMenu() {
            this.showCanned = true;
            this.loadCannedReplies();
        },

        useCanned(reply) {
            this.message = reply.body;
            this.showCanned = false;
        },

        emojiList() {
            return this.emojiData[this.emojiCategory] || [];
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
        },

        playSound() {
            try {
                const audio = new Audio('/voice/chat.wav');
                audio.volume = 0.7;
                audio.play().catch(() => {});
            } catch(e) {}
        }
    }
}
</script>
@endpush
