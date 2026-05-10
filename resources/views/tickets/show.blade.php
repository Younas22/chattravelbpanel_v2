@extends('tickets.layouts.app')
@section('title', 'Ticket #' . $ticket->ticket_number)

@section('content')
<div class="max-w-2xl">

    <div class="flex items-center justify-between mb-6">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="font-mono text-sm text-blue-600">{{ $ticket->ticket_number }}</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ticket->status === 'open' ? 'bg-green-100 text-green-700' : ($ticket->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-slate-100 text-slate-500') }}">
                    {{ ucfirst($ticket->status) }}
                </span>
            </div>
            <h1 class="text-xl font-bold text-slate-900">{{ $ticket->subject }}</h1>
        </div>
        <a href="{{ route('tickets.index') }}" class="text-sm text-slate-500 hover:text-slate-700">← My Tickets</a>
    </div>

    {{-- Messages --}}
    <div class="space-y-4 mb-6">
        @foreach($ticket->messages as $msg)
        <div class="bg-white rounded-2xl border border-slate-100 p-5 {{ $msg->sender_type === 'admin' ? 'border-l-4 border-l-blue-500' : '' }}">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-8 h-8 rounded-full {{ $msg->sender_type === 'admin' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600' }} flex items-center justify-center text-sm font-bold">
                    {{ $msg->sender_type === 'admin' ? 'S' : strtoupper(substr(auth('ticket_user')->user()->full_name, 0, 1)) }}
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-800">{{ $msg->sender_type === 'admin' ? 'Support Team' : auth('ticket_user')->user()->full_name }}</p>
                    <p class="text-xs text-slate-400">{{ $msg->created_at->format('M j, Y \a\t H:i') }}</p>
                </div>
            </div>
            <p class="text-sm text-slate-700 whitespace-pre-wrap">{!! nl2br(e($msg->body)) !!}</p>

            @if($msg->attachment_url)
            <div class="mt-3">
                <a href="{{ $msg->attachment_url }}" target="_blank"
                    class="inline-flex items-center gap-2 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm hover:bg-slate-100 transition-colors">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    {{ $msg->attachment_name }}
                </a>
            </div>
            @endif
        </div>
        @endforeach
    </div>

    {{-- Reply --}}
    @if($ticket->status !== 'closed')
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <h3 class="font-semibold text-slate-900 text-sm mb-3">Add Reply</h3>
        <form method="POST" action="{{ route('tickets.reply', $ticket) }}" enctype="multipart/form-data" class="space-y-3">
            @csrf
            @if($errors->any())
                <div class="p-3 bg-red-50 border border-red-100 text-red-600 text-sm rounded-xl">{{ $errors->first() }}</div>
            @endif
            <textarea name="body" rows="4" required placeholder="Your reply…"
                class="w-full resize-none px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('body') }}</textarea>
            <div class="flex items-center justify-between">
                <input type="file" name="attachment" class="text-sm text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-slate-100 file:text-slate-600 file:text-xs hover:file:bg-slate-200 transition-colors">
                <button type="submit" class="px-5 py-2 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition-colors text-sm cursor-pointer">Send Reply</button>
            </div>
        </form>
    </div>
    @else
    <div class="bg-slate-50 rounded-2xl border border-slate-200 p-5 text-center text-sm text-slate-500">
        This ticket is closed. <a href="{{ route('tickets.create') }}" class="text-blue-600 hover:underline">Open a new ticket</a> for further support.
    </div>
    @endif
</div>
@endsection
