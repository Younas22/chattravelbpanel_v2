@extends('admin.layouts.app')
@section('title', 'Tickets')

@section('content')
<div class="space-y-4">

    <div class="card !p-4 flex flex-wrap items-center gap-3">
        <form method="GET" class="flex flex-wrap gap-3 flex-1">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tickets, users…"
                class="flex-1 min-w-[200px] px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">

            <select name="status" onchange="this.form.submit()" class="px-3.5 py-2 rounded-xl border border-slate-200 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="all">All Status</option>
                <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
            </select>

            <select name="priority" onchange="this.form.submit()" class="px-3.5 py-2 rounded-xl border border-slate-200 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="all">All Priority</option>
                <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
            </select>

            <button type="submit" class="btn-primary">Search</button>
        </form>
    </div>

    <div class="card !p-0 overflow-hidden">
        @if($tickets->isEmpty())
            <div class="py-20 text-center">
                <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                <p class="text-slate-500 font-medium">No tickets found</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr class="text-left text-xs text-slate-500 uppercase tracking-wider">
                            <th class="px-5 py-3.5 font-medium">Ticket</th>
                            <th class="px-5 py-3.5 font-medium">User</th>
                            <th class="px-5 py-3.5 font-medium">Priority</th>
                            <th class="px-5 py-3.5 font-medium">Status</th>
                            <th class="px-5 py-3.5 font-medium">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($tickets as $ticket)
                        <tr class="hover:bg-slate-50 transition-colors {{ $ticket->unread_admin > 0 ? 'bg-blue-50/30' : '' }}">
                            <td class="px-5 py-4">
                                <a href="{{ route('admin.tickets.show', $ticket) }}" class="flex items-center gap-2">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono text-xs text-blue-600">{{ $ticket->ticket_number }}</span>
                                            @if($ticket->unread_admin > 0)
                                                <span class="badge bg-blue-600 text-white">{{ $ticket->unread_admin }}</span>
                                            @endif
                                        </div>
                                        <p class="font-medium text-slate-800 hover:text-blue-600">{{ Str::limit($ticket->subject, 50) }}</p>
                                    </div>
                                </a>
                            </td>
                            <td class="px-5 py-4">
                                <p class="font-medium text-slate-700">{{ $ticket->user->full_name }}</p>
                                <p class="text-xs text-slate-400">{{ $ticket->user->email }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <span class="badge {{ match($ticket->priority) {
                                    'urgent' => 'bg-red-100 text-red-700',
                                    'high' => 'bg-orange-100 text-orange-700',
                                    'medium' => 'bg-yellow-100 text-yellow-700',
                                    default => 'bg-slate-100 text-slate-600'
                                } }}">{{ ucfirst($ticket->priority) }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="badge {{ match($ticket->status) {
                                    'open' => 'bg-green-100 text-green-700',
                                    'pending' => 'bg-yellow-100 text-yellow-700',
                                    default => 'bg-slate-100 text-slate-500'
                                } }}">{{ ucfirst($ticket->status) }}</span>
                            </td>
                            <td class="px-5 py-4 text-slate-500 text-xs">{{ $ticket->created_at->format('M j, Y H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-4 border-t border-slate-100">{{ $tickets->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection
