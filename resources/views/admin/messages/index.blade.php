@extends('admin.layouts.app')
@section('title', 'Messages')

@section('content')
<div class="space-y-4">

    <div class="card !p-4 flex flex-wrap items-center gap-3">
        <form method="GET" class="flex flex-wrap gap-3 flex-1">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search conversations…"
                class="flex-1 min-w-[200px] px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit" class="btn-primary cursor-pointer">Search</button>
        </form>
        <a href="{{ route('admin.ticket-users.index') }}" class="btn-secondary cursor-pointer">Start a new conversation →</a>
    </div>

    <div class="card !p-0 overflow-hidden">
        @if($threads->isEmpty())
            <div class="py-20 text-center">
                <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                <p class="text-slate-500 font-medium">No conversations yet</p>
                <p class="text-slate-400 text-sm mt-1">Message a ticket user from the Ticket Users page to start one.</p>
            </div>
        @else
            <div class="divide-y divide-slate-50">
                @foreach($threads as $thread)
                <a href="{{ route('admin.messages.show', $thread) }}" class="flex items-center gap-4 px-5 py-4 hover:bg-slate-50 transition-colors">
                    <x-avatar :name="$thread->full_name" :image="$thread->profileImageUrl()" size-class="w-10 h-10" />
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-semibold text-slate-900 text-sm truncate">{{ $thread->full_name }}</p>
                            <span class="text-xs text-slate-400 shrink-0">{{ $thread->last_message?->created_at?->diffForHumans() }}</span>
                        </div>
                        <p class="text-xs text-slate-500 truncate">{{ $thread->last_message?->body ?: '📎 Attachment' }}</p>
                    </div>
                    @if($thread->unread_count > 0)
                        <span class="shrink-0 w-5 h-5 bg-blue-600 text-white text-[10px] rounded-full flex items-center justify-center font-bold">{{ $thread->unread_count > 9 ? '9+' : $thread->unread_count }}</span>
                    @endif
                </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
