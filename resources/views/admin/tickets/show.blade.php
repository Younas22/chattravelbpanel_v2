@extends('admin.layouts.app')
@section('title', 'Ticket #' . $ticket->ticket_number)

@section('content')
<div class="flex gap-6">

    {{-- Messages --}}
    <div class="flex-1 space-y-4">

        <div class="card !p-4 flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <span class="font-mono text-xs text-blue-600">{{ $ticket->ticket_number }}</span>
                    <span class="badge {{ $ticket->status === 'open' ? 'bg-green-100 text-green-700' : ($ticket->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-slate-100 text-slate-500') }}">{{ ucfirst($ticket->status) }}</span>
                    <span class="badge {{ $ticket->priority === 'urgent' ? 'bg-red-100 text-red-700' : ($ticket->priority === 'high' ? 'bg-orange-100 text-orange-700' : 'bg-slate-100 text-slate-600') }}">{{ ucfirst($ticket->priority) }}</span>
                </div>
                <h2 class="font-semibold text-slate-900 mt-1">{{ $ticket->subject }}</h2>
            </div>

            <div class="flex items-center gap-2">
                <select onchange="updateStatus(this.value)" class="text-sm px-3 py-1.5 rounded-xl border border-slate-200 bg-white focus:outline-none">
                    @foreach(['open', 'pending', 'closed'] as $s)
                        <option value="{{ $s }}" {{ $ticket->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <a href="{{ route('admin.tickets.index') }}" class="btn-secondary !py-1.5 !text-xs">← Back</a>
            </div>
        </div>

        {{-- Thread --}}
        <div class="space-y-4">
            @foreach($ticket->messages as $msg)
            <div class="card {{ $msg->sender_type === 'admin' ? 'border-l-4 border-l-blue-500' : '' }}">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full {{ $msg->sender_type === 'admin' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600' }} flex items-center justify-center text-sm font-semibold">
                            {{ $msg->sender_type === 'admin' ? 'A' : strtoupper(substr($ticket->user->full_name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-semibold text-sm text-slate-800">{{ $msg->sender_type === 'admin' ? 'Support Team' : $ticket->user->full_name }}</p>
                            <p class="text-xs text-slate-400">{{ $msg->created_at->format('M j, Y H:i') }}</p>
                        </div>
                    </div>
                </div>
                <div class="text-sm text-slate-700 whitespace-pre-wrap">{!! nl2br(e($msg->body)) !!}</div>

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

        {{-- Reply Form --}}
        @if($ticket->status !== 'closed')
        <div class="card">
            <h3 class="font-semibold text-slate-900 text-sm mb-3">Reply</h3>
            <form method="POST" action="{{ route('admin.tickets.reply', $ticket) }}" enctype="multipart/form-data">
                @csrf
                <textarea name="body" rows="5" required
                    placeholder="Type your reply…"
                    class="w-full resize-none rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 mb-3">{{ old('body') }}</textarea>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer text-sm text-slate-600 hover:text-slate-900 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                        Attach file
                        <input type="file" name="attachment" class="hidden" accept=".jpg,.jpeg,.png,.gif,.pdf,.zip,.txt">
                    </label>
                    <button type="submit" class="btn-primary">Send Reply</button>
                </div>
            </form>
        </div>
        @endif
    </div>

    {{-- Ticket Info --}}
    <div class="w-64 shrink-0 space-y-4">
        <div class="card">
            <h3 class="font-semibold text-slate-900 text-sm mb-3">Customer</h3>
            <div class="space-y-2 text-sm">
                <p class="font-medium text-slate-800">{{ $ticket->user->full_name }}</p>
                <p class="text-slate-500">{{ $ticket->user->email }}</p>
                @if($ticket->user->phone)
                    <p class="text-slate-500">{{ $ticket->user->phone }}</p>
                @endif
                @if($ticket->user->company_name)
                    <p class="text-slate-500">{{ $ticket->user->company_name }}</p>
                @endif
            </div>
        </div>

        <div class="card">
            <h3 class="font-semibold text-slate-900 text-sm mb-3">Priority</h3>
            <select onchange="updatePriority(this.value)" class="w-full text-sm px-3 py-2 rounded-xl border border-slate-200 bg-white focus:outline-none">
                @foreach(['low', 'medium', 'high', 'urgent'] as $p)
                    <option value="{{ $p }}" {{ $ticket->priority === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                @endforeach
            </select>
        </div>

        <div class="card text-sm text-slate-600 space-y-2">
            <div class="flex justify-between">
                <span class="text-slate-500">Created</span>
                <span>{{ $ticket->created_at->format('M j, Y') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Messages</span>
                <span>{{ $ticket->messages->count() }}</span>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function updateStatus(status) {
    fetch('{{ route('admin.tickets.status', $ticket) }}', {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ status })
    }).then(() => location.reload());
}
function updatePriority(priority) {
    fetch('{{ route('admin.tickets.priority', $ticket) }}', {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ priority })
    }).then(r => r.json()).then(() => {});
}
</script>
@endpush
