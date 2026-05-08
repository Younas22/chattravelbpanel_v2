@extends('admin.layouts.app')
@section('title', 'Visitors')

@section('content')
<div class="space-y-4" x-data="visitorsPage()">

    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2 text-sm text-slate-600">
            <span class="w-2.5 h-2.5 bg-green-500 rounded-full animate-pulse"></span>
            <span x-text="liveCount"></span> online now
        </div>
        <button @click="refresh()" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            Refresh
        </button>
    </div>

    {{-- Live Visitors --}}
    <div class="card">
        <h2 class="font-semibold text-slate-900 mb-4">Live Visitors</h2>
        <div class="space-y-2" id="live-visitors">
            <template x-for="v in liveVisitors" :key="v.id">
                <div class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 transition-colors">
                    <span class="w-2.5 h-2.5 bg-green-500 rounded-full shrink-0 animate-pulse"></span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-800" x-text="v.display_name || ('Visitor #' + v.id)"></p>
                        <p class="text-xs text-slate-500 truncate" x-text="v.current_page"></p>
                    </div>
                    <div class="text-right shrink-0 text-xs">
                        <span class="text-slate-600" x-text="v.country_code"></span>
                        <span class="mx-1 text-slate-300">•</span>
                        <span class="text-slate-400" x-text="v.browser"></span>
                    </div>
                </div>
            </template>
            <div x-show="liveVisitors.length === 0" class="py-8 text-center text-slate-400 text-sm">
                No visitors online right now
            </div>
        </div>
    </div>

    {{-- All Visitors --}}
    <div class="card !p-0 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="font-semibold text-slate-900">All Visitors</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr class="text-left text-xs text-slate-500 uppercase tracking-wider">
                        <th class="px-5 py-3 font-medium">Visitor</th>
                        <th class="px-5 py-3 font-medium">Location</th>
                        <th class="px-5 py-3 font-medium">Device</th>
                        <th class="px-5 py-3 font-medium">Landing Page</th>
                        <th class="px-5 py-3 font-medium">Last Seen</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($visitors as $visitor)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-3.5">
                            <p class="font-medium text-slate-800">{{ $visitor->display_name }}</p>
                            <p class="text-xs text-slate-400 font-mono">{{ $visitor->ip_address }}</p>
                        </td>
                        <td class="px-5 py-3.5 text-slate-600">{{ $visitor->country }} {{ $visitor->city ? ", $visitor->city" : '' }}</td>
                        <td class="px-5 py-3.5">
                            <span class="text-slate-600">{{ $visitor->browser }}</span>
                            <span class="text-slate-400 text-xs ml-1">/ {{ $visitor->os }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-slate-500 text-xs max-w-[200px]">
                            <span class="truncate block">{{ $visitor->landing_page }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-xs text-slate-500">{{ optional($visitor->last_activity_at)->diffForHumans() }}</td>
                        <td class="px-5 py-3.5">
                            @if($visitor->is_online)
                                <span class="flex items-center gap-1.5 text-green-600 text-xs font-medium">
                                    <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                                    Online
                                </span>
                            @else
                                <span class="text-slate-400 text-xs">Offline</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t border-slate-100">{{ $visitors->links() }}</div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function visitorsPage() {
    return {
        liveVisitors: [],
        liveCount: {{ $liveCount }},

        init() {
            this.refresh();
            setInterval(() => this.refresh(), 8000);
        },

        refresh() {
            fetch('{{ route('admin.visitors.live') }}')
                .then(r => r.json())
                .then(data => {
                    this.liveVisitors = data;
                    this.liveCount = data.length;
                });
        }
    }
}
</script>
@endpush
