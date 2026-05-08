@extends('admin.layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">

    {{-- Stats Grid --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Live Visitors</p>
                    <p class="text-3xl font-bold text-slate-900 mt-1" id="stat-visitors">{{ $stats['active_visitors'] }}</p>
                </div>
                <div class="w-12 h-12 bg-green-50 rounded-2xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1">
                <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                <span class="text-xs text-slate-500">Live now</span>
            </div>
        </div>

        <div class="card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Active Chats</p>
                    <p class="text-3xl font-bold text-slate-900 mt-1" id="stat-chats">{{ $stats['active_chats'] }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                </div>
            </div>
            <a href="{{ route('admin.conversations.index') }}" class="mt-3 text-xs text-blue-600 hover:underline flex items-center gap-1">
                View all <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        <div class="card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Unread</p>
                    <p class="text-3xl font-bold text-slate-900 mt-1" id="stat-unread">{{ $stats['unread_messages'] }}</p>
                </div>
                <div class="w-12 h-12 bg-orange-50 rounded-2xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
            </div>
            <p class="mt-3 text-xs text-slate-500">Messages pending reply</p>
        </div>

        <div class="card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Open Tickets</p>
                    <p class="text-3xl font-bold text-slate-900 mt-1" id="stat-tickets">{{ $stats['open_tickets'] }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-50 rounded-2xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                </div>
            </div>
            <a href="{{ route('admin.tickets.index') }}" class="mt-3 text-xs text-purple-600 hover:underline flex items-center gap-1">
                View all <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">

        {{-- Recent Conversations --}}
        <div class="lg:col-span-2 card">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-slate-900">Recent Chats</h2>
                <a href="{{ route('admin.conversations.index') }}" class="text-xs text-blue-600 hover:underline">View all</a>
            </div>

            @if($recentConversations->isEmpty())
                <div class="py-8 text-center">
                    <svg class="w-10 h-10 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                    <p class="text-sm text-slate-400">No active conversations</p>
                </div>
            @else
                <div class="space-y-2">
                    @foreach($recentConversations as $conv)
                    <a href="{{ route('admin.conversations.show', $conv) }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 transition-colors group">
                        <div class="w-9 h-9 bg-blue-100 rounded-full flex items-center justify-center text-blue-700 font-semibold text-sm shrink-0">
                            {{ strtoupper(substr($conv->visitor->display_name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-medium text-slate-900 truncate">{{ $conv->visitor->display_name }}</p>
                                @if($conv->unread_admin > 0)
                                    <span class="badge bg-blue-600 text-white">{{ $conv->unread_admin }}</span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-500 truncate">{{ $conv->latestMessage?->body ?: 'No messages yet' }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="badge {{ $conv->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">{{ $conv->status }}</span>
                            <p class="text-[10px] text-slate-400 mt-1">{{ $conv->updated_at->diffForHumans(short: true) }}</p>
                        </div>
                    </a>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Live Visitors --}}
        <div class="card">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-slate-900">Live Visitors</h2>
                <span class="flex items-center gap-1 text-xs text-green-600">
                    <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                    {{ $liveVisitors->count() }} online
                </span>
            </div>

            @if($liveVisitors->isEmpty())
                <div class="py-8 text-center">
                    <svg class="w-10 h-10 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <p class="text-sm text-slate-400">No visitors online</p>
                </div>
            @else
                <div class="space-y-2">
                    @foreach($liveVisitors as $visitor)
                    <div class="flex items-center gap-2 p-2 rounded-xl hover:bg-slate-50 transition-colors">
                        <span class="w-2 h-2 bg-green-500 rounded-full shrink-0 animate-pulse"></span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-800 truncate">{{ $visitor->display_name }}</p>
                            <p class="text-xs text-slate-500 truncate">{{ $visitor->current_page }}</p>
                        </div>
                        <span class="text-xs text-slate-400 shrink-0">{{ $visitor->country_code }}</span>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Recent Tickets --}}
    @if($recentTickets->isNotEmpty())
    <div class="card">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-slate-900">Open Tickets</h2>
            <a href="{{ route('admin.tickets.index') }}" class="text-xs text-blue-600 hover:underline">View all</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-slate-500 uppercase tracking-wider border-b border-slate-100">
                        <th class="pb-3 font-medium">#</th>
                        <th class="pb-3 font-medium">Subject</th>
                        <th class="pb-3 font-medium">User</th>
                        <th class="pb-3 font-medium">Priority</th>
                        <th class="pb-3 font-medium">Status</th>
                        <th class="pb-3 font-medium">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($recentTickets as $ticket)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-3 pr-4"><a href="{{ route('admin.tickets.show', $ticket) }}" class="text-blue-600 hover:underline font-mono text-xs">{{ $ticket->ticket_number }}</a></td>
                        <td class="py-3 pr-4"><a href="{{ route('admin.tickets.show', $ticket) }}" class="font-medium text-slate-800 hover:text-blue-600 transition-colors">{{ Str::limit($ticket->subject, 40) }}</a></td>
                        <td class="py-3 pr-4 text-slate-600">{{ $ticket->user->full_name }}</td>
                        <td class="py-3 pr-4">
                            <span class="badge {{ $ticket->priority === 'urgent' ? 'bg-red-100 text-red-700' : ($ticket->priority === 'high' ? 'bg-orange-100 text-orange-700' : 'bg-slate-100 text-slate-600') }}">
                                {{ ucfirst($ticket->priority) }}
                            </span>
                        </td>
                        <td class="py-3 pr-4">
                            <span class="badge bg-blue-100 text-blue-700">{{ ucfirst($ticket->status) }}</span>
                        </td>
                        <td class="py-3 text-slate-500 text-xs">{{ $ticket->created_at->format('M j, Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
// Auto-refresh stats
setInterval(function() {
    fetch('{{ route('admin.stats') }}')
        .then(r => r.json())
        .then(data => {
            document.getElementById('stat-visitors').textContent = data.active_visitors;
            document.getElementById('stat-chats').textContent = data.active_chats;
            document.getElementById('stat-unread').textContent = data.unread_messages;
            document.getElementById('stat-tickets').textContent = data.open_tickets;
        });
}, 8000);
</script>
@endpush
