@extends('admin.layouts.app')
@section('title', 'Live Chats')

@section('content')
<div class="space-y-4">

    {{-- Filters --}}
    <div class="card !p-4 flex flex-wrap items-center gap-3">
        <form method="GET" class="flex flex-wrap gap-3 flex-1">
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Search visitor, email, IP…"
                class="flex-1 min-w-[200px] px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">

            <select name="status" onchange="this.form.submit()"
                class="px-3.5 py-2 rounded-xl border border-slate-200 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="all" {{ request('status','all') === 'all' ? 'selected' : '' }}>All Status</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
            </select>

            <button type="submit" class="btn-primary">Search</button>
        </form>
    </div>

    {{-- Conversations List --}}
    <div class="card !p-0 overflow-hidden">
        @if($conversations->isEmpty())
            <div class="py-20 text-center">
                <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                <p class="text-slate-500 font-medium">No conversations found</p>
                <p class="text-slate-400 text-sm mt-1">Conversations will appear here when visitors start chatting</p>
            </div>
        @else
            <div class="divide-y divide-slate-50">
                @foreach($conversations as $conv)
                <a href="{{ route('admin.conversations.show', $conv) }}"
                    class="flex items-center gap-4 px-5 py-4 hover:bg-slate-50 transition-colors {{ $conv->unread_admin > 0 ? 'bg-blue-50/40' : '' }}">

                    {{-- Avatar --}}
                    <div class="relative shrink-0">
                        <div class="w-10 h-10 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center font-semibold text-sm">
                            {{ strtoupper(substr($conv->visitor->display_name, 0, 1)) }}
                        </div>
                        @if($conv->visitor->is_online)
                            <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white"></span>
                        @endif
                    </div>

                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="font-semibold text-sm text-slate-900">{{ $conv->visitor->display_name }}</p>
                            @if($conv->unread_admin > 0)
                                <span class="w-5 h-5 bg-blue-600 text-white text-[10px] rounded-full flex items-center justify-center font-bold">{{ $conv->unread_admin }}</span>
                            @endif
                        </div>
                        <p class="text-sm text-slate-500 truncate">{{ $conv->latestMessage?->body ?: 'No messages yet' }}</p>
                        <div class="flex items-center gap-2 mt-1">
                            @if($conv->visitor->country)
                                <span class="text-xs text-slate-400">{{ $conv->visitor->country }}</span>
                            @endif
                            @if($conv->visitor->current_page)
                                <span class="text-xs text-slate-300">•</span>
                                <span class="text-xs text-slate-400 truncate max-w-[200px]">{{ $conv->visitor->current_page }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- Status & Time --}}
                    <div class="text-right shrink-0">
                        <span class="badge {{ $conv->status === 'active' ? 'bg-green-100 text-green-700' : ($conv->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-slate-100 text-slate-500') }}">
                            {{ ucfirst($conv->status) }}
                        </span>
                        <p class="text-xs text-slate-400 mt-1">{{ $conv->updated_at->diffForHumans(short: true) }}</p>
                    </div>
                </a>
                @endforeach
            </div>

            <div class="px-5 py-4 border-t border-slate-100">
                {{ $conversations->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
