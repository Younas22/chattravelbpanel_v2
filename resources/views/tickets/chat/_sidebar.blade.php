@php
    $activeType = $activeType ?? null;
    $supportUnread = $supportUnread ?? 0;
@endphp
<div class="w-72 shrink-0 flex flex-col bg-white border border-slate-100 rounded-2xl overflow-hidden h-full">
    <div class="px-4 py-3 border-b border-slate-100 shrink-0">
        <span class="font-semibold text-slate-900 text-sm">Chat</span>
    </div>

    <div class="flex-1 overflow-y-auto divide-y divide-slate-50">
        <a href="{{ route('tickets.chat.support.show') }}"
           class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 transition-colors {{ $activeType === 'support' ? 'bg-brand-50 border-l-2 border-brand-500' : '' }}">
            <div class="w-9 h-9 bg-brand-600 text-white rounded-full flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-1">
                    <span class="text-sm font-semibold text-slate-900 truncate">Support Team</span>
                    @if($supportUnread > 0)
                        <span class="shrink-0 w-4 h-4 bg-brand-600 text-white text-[9px] rounded-full flex items-center justify-center font-bold">{{ $supportUnread > 9 ? '9+' : $supportUnread }}</span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 truncate">Message our admin team directly</p>
            </div>
        </a>
    </div>
</div>
