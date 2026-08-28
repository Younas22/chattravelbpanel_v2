@extends('admin.layouts.app')
@section('title', 'Leads')

@section('content')
<div class="space-y-4">

    {{-- Status filter tabs --}}
    <div class="card !p-3 flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.leads.index') }}"
            class="px-3.5 py-1.5 rounded-xl text-sm font-medium transition-colors {{ $status === 'all' ? 'bg-blue-600 text-white' : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }}">
            All <span class="opacity-70">({{ $total }})</span>
        </a>
        @foreach(\App\Models\Visitor::LEAD_STATUSES as $key => $label)
        <a href="{{ route('admin.leads.index', ['status' => $key]) }}"
            class="px-3.5 py-1.5 rounded-xl text-sm font-medium transition-colors {{ $status === $key ? 'bg-blue-600 text-white' : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }}">
            {{ $label }} <span class="opacity-70">({{ $counts[$key] ?? 0 }})</span>
        </a>
        @endforeach
    </div>

    <div class="card !p-0 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="font-semibold text-slate-900">Leads</h2>
            <p class="text-xs text-slate-400 mt-0.5">Visitors who shared their name, email or WhatsApp number before starting a chat</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr class="text-left text-xs text-slate-500 uppercase tracking-wider">
                        <th class="px-5 py-3 font-medium">Name</th>
                        <th class="px-5 py-3 font-medium">Email</th>
                        <th class="px-5 py-3 font-medium">WhatsApp</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 font-medium">Status Updated</th>
                        <th class="px-5 py-3 font-medium">Submitted</th>
                        <th class="px-5 py-3 font-medium"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($leads as $lead)
                    <tr class="hover:bg-slate-50 transition-colors" id="lead-row-{{ $lead->id }}">
                        <td class="px-5 py-3.5">
                            <p class="font-medium text-slate-800">{{ $lead->display_name }}</p>
                            <p class="text-xs text-slate-400 font-mono">{{ $lead->ip_address }}</p>
                        </td>
                        <td class="px-5 py-3.5 text-slate-600">{{ $lead->email ?: '—' }}</td>
                        <td class="px-5 py-3.5">
                            @if($lead->whatsapp)
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $lead->whatsapp) }}" target="_blank" rel="noopener"
                                   class="text-green-600 hover:text-green-700 font-medium">{{ $lead->whatsapp }}</a>
                            @else
                                <span class="text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            <select
                                onchange="updateLeadStatus({{ $lead->id }}, this.value)"
                                class="text-xs font-semibold rounded-lg border-0 py-1.5 pl-2.5 pr-7 cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500"
                                data-status-select="{{ $lead->id }}">
                                @foreach(\App\Models\Visitor::LEAD_STATUSES as $key => $label)
                                    <option value="{{ $key }}" {{ $lead->lead_status === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-5 py-3.5 text-xs text-slate-500" id="lead-updated-{{ $lead->id }}">
                            {{ $lead->lead_status_updated_at ? $lead->lead_status_updated_at->format('M j, H:i') : '—' }}
                        </td>
                        <td class="px-5 py-3.5 text-xs text-slate-500">{{ $lead->created_at->diffForHumans() }}</td>
                        <td class="px-5 py-3.5 text-right">
                            @if($lead->latestConversation)
                                <a href="{{ route('admin.conversations.show', $lead->latestConversation) }}" class="btn-primary !py-1.5 !text-xs">Chat</a>
                            @else
                                <span class="text-slate-300 text-xs">No chat yet</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-slate-400 text-sm">No leads found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t border-slate-100">{{ $leads->links() }}</div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const LEAD_STATUS_COLORS = {
    pending:   'bg-yellow-100 text-yellow-700',
    contacted: 'bg-blue-100 text-blue-700',
    follow_up: 'bg-purple-100 text-purple-700',
    converted: 'bg-green-100 text-green-700',
    closed:    'bg-slate-200 text-slate-600',
};

function leadStatusSelectClass(status) {
    return 'text-xs font-semibold rounded-lg border-0 py-1.5 pl-2.5 pr-7 cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500 '
        + (LEAD_STATUS_COLORS[status] || 'bg-slate-100 text-slate-600');
}

function updateLeadStatus(visitorId, status) {
    const select = document.querySelector(`[data-status-select="${visitorId}"]`);
    fetch(`/admin/leads/${visitorId}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ status }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            const updatedEl = document.getElementById(`lead-updated-${visitorId}`);
            if (updatedEl) updatedEl.textContent = data.updated_at;
            if (select) select.className = leadStatusSelectClass(data.status);
        }
    })
    .catch(() => {});
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-status-select]').forEach(sel => {
        sel.className = leadStatusSelectClass(sel.value);
    });
});
</script>
@endpush
