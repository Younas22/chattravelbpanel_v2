@php
    $activeType = $activeType ?? null;
    $activeId = $activeId ?? null;
@endphp
<div class="w-72 shrink-0 flex flex-col bg-white border border-slate-100 rounded-2xl overflow-hidden h-full">
    <div class="px-4 py-3 border-b border-slate-100 shrink-0">
        <span class="font-semibold text-slate-900 text-sm">Chat</span>
    </div>

    <div class="flex-1 overflow-y-auto divide-y divide-slate-50">
        <div class="px-4 pt-3 pb-1">
            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Groups</p>
        </div>

        @forelse($groups as $g)
        <a href="{{ route('tickets.chat.show', $g) }}"
           class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 transition-colors {{ $activeType === 'group' && $activeId === $g->id ? 'bg-blue-50 border-l-2 border-blue-500' : '' }}">
            <div class="w-9 h-9 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center font-semibold text-sm shrink-0">
                {{ strtoupper(substr($g->name, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-1">
                    <span class="text-sm font-semibold text-slate-900 truncate">{{ $g->name }}</span>
                    @if($g->unread_count > 0)
                        <span class="shrink-0 w-4 h-4 bg-blue-600 text-white text-[9px] rounded-full flex items-center justify-center font-bold">{{ $g->unread_count > 9 ? '9+' : $g->unread_count }}</span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 truncate">{{ $g->latestMessage?->body ?: ($g->members_count . ' members') }}</p>
            </div>
        </a>
        @empty
        <p class="px-4 py-3 text-xs text-slate-400">No groups yet</p>
        @endforelse

        <div class="px-4 pt-4 pb-1">
            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">People</p>
        </div>

        @forelse($contacts as $c)
        <a href="{{ route('tickets.chat.dm.show', $c) }}"
           class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 transition-colors {{ $activeType === 'dm' && $activeId === $c->id ? 'bg-blue-50 border-l-2 border-blue-500' : '' }}">
            <div class="w-9 h-9 bg-slate-100 text-slate-600 rounded-full flex items-center justify-center font-semibold text-sm shrink-0">
                {{ strtoupper(substr($c->full_name, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-1">
                    <span class="text-sm font-semibold text-slate-900 truncate">{{ $c->full_name }}</span>
                    @if($c->unread_count > 0)
                        <span class="shrink-0 w-4 h-4 bg-blue-600 text-white text-[9px] rounded-full flex items-center justify-center font-bold">{{ $c->unread_count > 9 ? '9+' : $c->unread_count }}</span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 truncate">{{ $c->last_message?->body ?: 'Say hello' }}</p>
            </div>
        </a>
        @empty
        <p class="px-4 py-3 text-xs text-slate-400">No one to message yet</p>
        @endforelse
    </div>
</div>
