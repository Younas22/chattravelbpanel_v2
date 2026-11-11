@extends('admin.layouts.app')
@section('title', 'Messages')

@section('content')
<div class="max-w-4xl mx-auto space-y-3">

    <div class="flex flex-col sm:flex-row gap-2">
        <form method="GET" class="flex gap-2 flex-1">
            <div class="relative flex-1">
                <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email…"
                    class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>
            <button type="submit" class="btn-primary">Search</button>
        </form>
        <a href="{{ route('admin.ticket-users.index') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New conversation
        </a>
    </div>

    <div class="card !p-0 overflow-hidden">
        @if($threads->isEmpty())
            <div class="py-20 px-6 text-center">
                <div class="w-14 h-14 rounded-full bg-brand-50 text-brand-500 grid place-items-center mx-auto mb-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                </div>
                <p class="text-slate-600 font-medium">No conversations yet</p>
                <p class="text-slate-400 text-sm mt-1">Message a ticket user from the Ticket Users page to start one.</p>
            </div>
        @else
            <div class="divide-y divide-slate-100">
                @foreach($threads as $thread)
                <a href="{{ route('admin.messages.show', $thread) }}" class="chat-list-item !py-3.5 sm:!px-5 {{ $thread->unread_count > 0 ? 'bg-brand-50/40' : '' }}">
                    <div class="relative shrink-0">
                        <x-avatar :name="$thread->full_name" :image="$thread->profileImageUrl()" size-class="w-12 h-12" />
                        @if($thread->isOnline())
                            <span class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-green-500 rounded-full border-2 border-white"></span>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-baseline justify-between gap-2">
                            <p class="text-sm truncate {{ $thread->unread_count > 0 ? 'font-bold text-slate-900' : 'font-semibold text-slate-800' }}">{{ $thread->full_name }}</p>
                            <span class="text-[11px] shrink-0 {{ $thread->unread_count > 0 ? 'text-brand-600 font-semibold' : 'text-slate-400' }}">{{ $thread->last_message?->created_at?->diffForHumans(short: true) }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-2 mt-0.5">
                            <p class="text-sm truncate {{ $thread->unread_count > 0 ? 'text-slate-700' : 'text-slate-500' }}">
                                @if($thread->last_message?->sender_type === 'admin')<span class="text-slate-400">You: </span>@endif{{ $thread->last_message?->body ?: '📎 Attachment' }}
                            </p>
                            @if($thread->unread_count > 0)
                                <span class="unread-dot">{{ $thread->unread_count > 9 ? '9+' : $thread->unread_count }}</span>
                            @endif
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
