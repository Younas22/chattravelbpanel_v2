@php
    $activeType = $activeType ?? null;
    $supportUnread = $supportUnread ?? 0;
@endphp
{{-- Desktop-only chat list; on mobile the chat itself fills the screen --}}
<aside class="hidden lg:flex w-80 shrink-0 flex-col bg-white border border-slate-100 rounded-2xl overflow-hidden shadow-sm h-full">
    <div class="px-4 h-14 flex items-center border-b border-slate-100 shrink-0">
        <span class="font-semibold text-slate-900">Chats</span>
    </div>

    <div class="flex-1 overflow-y-auto thin-scroll">
        <a href="{{ route('tickets.chat.support.show') }}" class="chat-list-item {{ $activeType === 'support' ? 'is-active' : '' }}">
            <div class="w-10 h-10 bg-brand-600 text-white rounded-full grid place-items-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-sm font-semibold text-slate-900 truncate">Support Team</span>
                    @if($supportUnread > 0)
                        <span class="unread-dot">{{ $supportUnread > 9 ? '9+' : $supportUnread }}</span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 truncate">Message our admin team directly</p>
            </div>
        </a>
    </div>
</aside>
