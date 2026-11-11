@extends('admin.layouts.app')
@section('title', 'Live Chats')

@section('content')
<div class="max-w-4xl mx-auto space-y-3">

    {{-- Search + status filter --}}
    <form method="GET" class="flex flex-col sm:flex-row gap-2">
        <div class="relative flex-1">
            <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search visitor, email, IP…"
                class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
        </div>
        <input type="hidden" name="status" value="{{ request('status', 'all') }}">
        <button type="submit" class="btn-primary sm:w-auto">Search</button>
    </form>

    {{-- Status tabs --}}
    <div class="flex gap-1.5 overflow-x-auto thin-scroll pb-1">
        @foreach(['all' => 'All', 'pending' => 'Pending', 'active' => 'Active', 'closed' => 'Closed'] as $key => $label)
            <a href="{{ request()->fullUrlWithQuery(['status' => $key, 'page' => null]) }}"
               class="shrink-0 px-3.5 py-1.5 rounded-full text-sm font-medium transition-colors {{ request('status', 'all') === $key ? 'bg-brand-600 text-white shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Conversations --}}
    <div class="card !p-0 overflow-hidden">
        @if($conversations->isEmpty())
            <div class="py-20 px-6 text-center">
                <div class="w-14 h-14 rounded-full bg-brand-50 text-brand-500 grid place-items-center mx-auto mb-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                </div>
                <p class="text-slate-600 font-medium">No conversations found</p>
                <p class="text-slate-400 text-sm mt-1">Conversations will appear here when visitors start chatting</p>
            </div>
        @else
            <div class="divide-y divide-slate-100">
                @foreach($conversations as $conv)
                @php
                    $dot = $conv->status === 'active' ? 'bg-green-500' : ($conv->status === 'pending' ? 'bg-yellow-500' : 'bg-slate-300');
                @endphp
                <a href="{{ route('admin.conversations.show', $conv) }}" class="chat-list-item !py-3.5 sm:!px-5 {{ $conv->unread_admin > 0 ? 'bg-brand-50/40' : '' }}">
                    <div class="relative shrink-0">
                        <div class="w-12 h-12 bg-brand-100 text-brand-700 rounded-full grid place-items-center font-semibold">
                            {{ strtoupper(substr($conv->visitor->display_name, 0, 1)) }}
                        </div>
                        @if($conv->visitor->is_online)
                            <span class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-green-500 rounded-full border-2 border-white"></span>
                        @endif
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-baseline justify-between gap-2">
                            <p class="text-sm truncate {{ $conv->unread_admin > 0 ? 'font-bold text-slate-900' : 'font-semibold text-slate-800' }}">{{ $conv->visitor->display_name }}</p>
                            <span class="text-[11px] shrink-0 {{ $conv->unread_admin > 0 ? 'text-brand-600 font-semibold' : 'text-slate-400' }}">{{ $conv->updated_at->diffForHumans(short: true) }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-2 mt-0.5">
                            <p class="text-sm truncate {{ $conv->unread_admin > 0 ? 'text-slate-700' : 'text-slate-500' }}">{{ $conv->latestMessage?->body ?: 'No messages yet' }}</p>
                            @if($conv->unread_admin > 0)
                                <span class="unread-dot">{{ $conv->unread_admin > 9 ? '9+' : $conv->unread_admin }}</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-1.5 mt-1 text-xs text-slate-400 min-w-0">
                            <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $dot }}"></span>
                            <span class="shrink-0">{{ ucfirst($conv->status) }}</span>
                            @if($conv->visitor->country)
                                <span class="shrink-0">· {{ $conv->visitor->country }}</span>
                            @endif
                            @if($conv->visitor->current_page)
                                <span class="truncate hidden sm:inline">· {{ $conv->visitor->current_page }}</span>
                            @endif
                        </div>
                    </div>
                </a>
                @endforeach
            </div>

            @if($conversations->hasPages())
            <div class="px-4 sm:px-5 py-4 border-t border-slate-100">
                {{ $conversations->withQueryString()->links() }}
            </div>
            @endif
        @endif
    </div>
</div>
@endsection
