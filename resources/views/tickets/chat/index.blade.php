@extends('tickets.layouts.chat')
@section('title', 'Chat')

@section('content')
<div class="flex h-full gap-4">
    @include('tickets.chat._sidebar')

    <div class="flex-1 card !p-0 overflow-hidden flex items-center justify-center">
        <div class="text-center text-slate-400 max-w-xs px-4">
            <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
            <p class="text-sm font-medium text-slate-500">Select a group or person from the left to start chatting.</p>
        </div>
    </div>
</div>
@endsection
