@extends('tickets.layouts.app')
@section('title', 'My Tickets')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-slate-900">My Tickets</h1>
        <a href="{{ route('tickets.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Ticket
        </a>
    </div>

    @if($tickets->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-100 p-16 text-center">
            <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
            <p class="text-slate-600 font-medium">No tickets yet</p>
            <p class="text-slate-400 text-sm mt-1">Create a ticket to get help from our support team</p>
            <a href="{{ route('tickets.create') }}" class="inline-flex mt-4 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700 transition-colors">Create First Ticket</a>
        </div>
    @else
        <div class="space-y-3">
            @foreach($tickets as $ticket)
            <a href="{{ route('tickets.show', $ticket) }}"
                class="flex items-center gap-4 bg-white border border-slate-100 rounded-2xl p-5 hover:border-blue-200 hover:shadow-sm transition-all {{ $ticket->unread_user > 0 ? 'border-l-4 border-l-blue-500' : '' }}">

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="font-mono text-xs text-blue-600">{{ $ticket->ticket_number }}</span>
                        @if($ticket->unread_user > 0)
                            <span class="w-5 h-5 bg-blue-600 text-white text-[10px] rounded-full flex items-center justify-center font-bold">{{ $ticket->unread_user }}</span>
                        @endif
                    </div>
                    <p class="font-semibold text-slate-900">{{ $ticket->subject }}</p>
                    <p class="text-sm text-slate-500 mt-1">{{ $ticket->created_at->format('M j, Y') }}</p>
                </div>

                <div class="text-right shrink-0">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ticket->status === 'open' ? 'bg-green-100 text-green-700' : ($ticket->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-slate-100 text-slate-500') }}">
                        {{ ucfirst($ticket->status) }}
                    </span>
                    <p class="text-xs text-slate-400 mt-1">{{ $ticket->messages->count() }} replies</p>
                </div>
            </a>
            @endforeach
        </div>

        <div>{{ $tickets->links() }}</div>
    @endif
</div>
@endsection
