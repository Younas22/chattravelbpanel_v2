@extends('tickets.layouts.app')
@section('title', $group->name)

@section('content')
<div class="max-w-2xl">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-slate-900">{{ $group->name }}</h1>
            <p class="text-xs text-slate-400 mt-0.5">{{ $group->members->count() }} member{{ $group->members->count() !== 1 ? 's' : '' }}</p>
        </div>
        <a href="{{ route('tickets.chat.index') }}" class="text-sm text-slate-500 hover:text-slate-700">← Chat</a>
    </div>

    {{-- Messages --}}
    <div id="messages-container" class="space-y-4 mb-6 max-h-[55vh] overflow-y-auto">
        @forelse($group->messages as $msg)
        <div class="bg-white rounded-2xl border border-slate-100 p-5 {{ $msg->sender_type === 'admin' ? 'border-l-4 border-l-blue-500' : '' }}">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-8 h-8 rounded-full {{ $msg->sender_type === 'admin' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600' }} flex items-center justify-center text-sm font-bold">
                    {{ strtoupper(substr($msg->sender_name, 0, 1)) }}
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-800">{{ $msg->sender_type === 'admin' ? 'Support Team' : $msg->sender_name }}</p>
                    <p class="text-xs text-slate-400">{{ $msg->created_at->format('M j, Y \a\t H:i') }}</p>
                </div>
            </div>
            @if($msg->body)
                <p class="text-sm text-slate-700 whitespace-pre-wrap">{!! nl2br(e($msg->body)) !!}</p>
            @endif

            @if($msg->attachment_url)
            <div class="mt-3">
                @if($msg->attachment_type === 'image')
                    <img src="{{ $msg->attachment_url }}" class="max-w-full rounded-xl max-h-72 object-cover cursor-pointer" onclick="window.open('{{ $msg->attachment_url }}','_blank')">
                @else
                    <a href="{{ $msg->attachment_url }}" target="_blank" class="inline-flex items-center gap-2 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm hover:bg-slate-100 transition-colors">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        {{ $msg->attachment_name }}
                    </a>
                @endif
            </div>
            @endif
        </div>
        @empty
        <div class="bg-slate-50 rounded-2xl border border-slate-200 p-8 text-center text-sm text-slate-500">No messages yet. Say hello!</div>
        @endforelse
    </div>

    {{-- Reply --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <h3 class="font-semibold text-slate-900 text-sm mb-3">Send Message</h3>
        <div id="chat-error" class="hidden mb-3 p-3 bg-red-50 border border-red-100 text-red-600 text-sm rounded-xl"></div>
        <div id="file-preview" class="hidden mb-2 p-2.5 bg-slate-50 rounded-xl border border-slate-200 flex items-center gap-2">
            <span id="file-preview-name" class="text-xs text-slate-700 truncate flex-1"></span>
            <button type="button" onclick="clearFile()" class="text-slate-400 hover:text-red-500 cursor-pointer">✕</button>
        </div>
        <div class="space-y-3">
            <textarea id="chat-body" rows="3" placeholder="Your message…"
                class="w-full resize-none px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            <div class="flex items-center justify-between">
                <input type="file" id="chat-attachment" class="text-sm text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-slate-100 file:text-slate-600 file:text-xs hover:file:bg-slate-200 transition-colors" onchange="previewFile(this)">
                <button type="button" id="send-btn" onclick="sendMessage()" class="px-5 py-2 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition-colors text-sm cursor-pointer">Send</button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    let lastMessageId = {{ $group->messages->last()?->id ?? 0 }};
    let sending = false;
    let selectedFile = null;
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const pollUrl = '{{ route('tickets.chat.poll', $group) }}';
    const sendUrl = '{{ route('tickets.chat.message', $group) }}';

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function renderMessage(m) {
        const isAdmin = m.sender_type === 'admin';
        const initial = (m.sender_name || '?').charAt(0).toUpperCase();
        const name = isAdmin ? 'Support Team' : escapeHtml(m.sender_name || 'Member');
        const time = new Date(m.created_at).toLocaleString([], { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });

        let attachmentHtml = '';
        if (m.attachment_url) {
            if (m.attachment_type === 'image') {
                attachmentHtml = `<div class="mt-3"><img src="${m.attachment_url}" class="max-w-full rounded-xl max-h-72 object-cover cursor-pointer" onclick="window.open('${m.attachment_url}','_blank')"></div>`;
            } else {
                attachmentHtml = `<div class="mt-3"><a href="${m.attachment_url}" target="_blank" class="inline-flex items-center gap-2 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm hover:bg-slate-100 transition-colors">📎 ${escapeHtml(m.attachment_name || 'Attachment')}</a></div>`;
            }
        }

        const bodyHtml = m.body ? `<p class="text-sm text-slate-700 whitespace-pre-wrap">${escapeHtml(m.body).replace(/\n/g, '<br>')}</p>` : '';

        const div = document.createElement('div');
        div.className = `bg-white rounded-2xl border border-slate-100 p-5 ${isAdmin ? 'border-l-4 border-l-blue-500' : ''}`;
        div.innerHTML = `
            <div class="flex items-center gap-3 mb-3">
                <div class="w-8 h-8 rounded-full ${isAdmin ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600'} flex items-center justify-center text-sm font-bold">${initial}</div>
                <div>
                    <p class="text-sm font-semibold text-slate-800">${name}</p>
                    <p class="text-xs text-slate-400">${time}</p>
                </div>
            </div>
            ${bodyHtml}
            ${attachmentHtml}
        `;
        return div;
    }

    function scrollToBottom() {
        const c = document.getElementById('messages-container');
        c.scrollTop = c.scrollHeight;
    }

    function pollMessages() {
        fetch(`${pollUrl}?after_id=${lastMessageId}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                if (data.messages && data.messages.length) {
                    const container = document.getElementById('messages-container');
                    const emptyState = container.querySelector('.bg-slate-50');
                    if (emptyState) emptyState.remove();
                    data.messages.forEach(m => {
                        container.appendChild(renderMessage(m));
                        lastMessageId = Math.max(lastMessageId, m.id);
                    });
                    scrollToBottom();
                }
            })
            .catch(() => {});
    }

    window.previewFile = function (input) {
        const file = input.files[0];
        if (!file) return;
        selectedFile = file;
        document.getElementById('file-preview-name').textContent = file.name;
        document.getElementById('file-preview').classList.remove('hidden');
    };

    window.clearFile = function () {
        selectedFile = null;
        document.getElementById('chat-attachment').value = '';
        document.getElementById('file-preview').classList.add('hidden');
    };

    window.sendMessage = function () {
        const bodyEl = document.getElementById('chat-body');
        const body = bodyEl.value.trim();
        const errorEl = document.getElementById('chat-error');
        errorEl.classList.add('hidden');

        if (!body && !selectedFile) return;
        if (sending) return;
        sending = true;
        document.getElementById('send-btn').disabled = true;

        const formData = new FormData();
        if (body) formData.append('body', body);
        if (selectedFile) formData.append('attachment', selectedFile);

        fetch(sendUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf },
            body: formData,
        })
        .then(r => r.json().then(data => ({ status: r.status, data })))
        .then(({ status, data }) => {
            sending = false;
            document.getElementById('send-btn').disabled = false;
            if (status >= 400) {
                errorEl.textContent = data.error || 'Something went wrong.';
                errorEl.classList.remove('hidden');
                return;
            }
            const container = document.getElementById('messages-container');
            const emptyState = container.querySelector('.bg-slate-50');
            if (emptyState) emptyState.remove();
            container.appendChild(renderMessage({
                sender_type: 'admin' === data.message.sender_type ? 'admin' : 'ticket_user',
                sender_name: data.message.sender_name,
                body: data.message.body,
                attachment_url: data.attachment_url,
                attachment_name: data.message.attachment_name,
                attachment_type: data.message.attachment_type,
                created_at: data.message.created_at,
            }));
            lastMessageId = Math.max(lastMessageId, data.message.id);
            scrollToBottom();
            bodyEl.value = '';
            clearFile();
        })
        .catch(() => {
            sending = false;
            document.getElementById('send-btn').disabled = false;
            errorEl.textContent = 'Network error. Please try again.';
            errorEl.classList.remove('hidden');
        });
    };

    scrollToBottom();
    setInterval(pollMessages, 4000);
})();
</script>
@endsection
