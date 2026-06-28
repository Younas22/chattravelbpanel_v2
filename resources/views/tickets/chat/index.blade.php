@extends('tickets.layouts.app')
@section('title', 'Chat')

@section('content')
<div class="max-w-2xl">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold text-slate-900">Chat</h1>
        <a href="{{ route('tickets.index') }}" class="text-sm text-slate-500 hover:text-slate-700">My Tickets →</a>
    </div>

    @if($groups->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-100 p-10 text-center">
            <p class="text-slate-500">You're not part of any group chat yet.</p>
            <p class="text-sm text-slate-400 mt-1">Once our support team adds you to a group, it will show up here.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($groups as $group)
            <a href="{{ route('tickets.chat.show', $group) }}" class="flex items-center gap-4 bg-white rounded-2xl border border-slate-100 p-4 hover:border-blue-200 hover:bg-blue-50/30 transition-colors">
                <div class="relative shrink-0">
                    <div class="w-11 h-11 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center font-semibold">
                        {{ strtoupper(substr($group->name, 0, 1)) }}
                    </div>
                    @if($group->unread_count > 0)
                        <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-[9px] rounded-full flex items-center justify-center font-bold">{{ $group->unread_count > 9 ? '9+' : $group->unread_count }}</span>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-slate-900 text-sm truncate">{{ $group->name }}</p>
                    <p class="text-xs text-slate-500 truncate">{{ $group->latestMessage?->body ?: ($group->members_count . ' members') }}</p>
                </div>
                <svg class="w-4 h-4 text-slate-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
