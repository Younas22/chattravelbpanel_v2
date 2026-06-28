@extends('admin.layouts.app')
@section('title', 'Ticket Users')

@section('content')
<div class="space-y-4" x-data="ticketUserManager()">

    <div class="card !p-4 flex flex-wrap items-center gap-3">
        <form method="GET" class="flex flex-wrap gap-3 flex-1">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, email or phone…"
                class="flex-1 min-w-[200px] px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit" class="btn-primary cursor-pointer">Search</button>
        </form>
        <div class="text-sm text-slate-500">Total: <span class="font-semibold text-slate-800">{{ $users->total() }}</span> users</div>
        <button @click="showModal = true" class="btn-primary cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add User
        </button>
    </div>

    <div class="card !p-0 overflow-hidden">
        @if($users->isEmpty())
            <div class="py-20 text-center">
                <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <p class="text-slate-500 font-medium">No ticket users found</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr class="text-left text-xs text-slate-500 uppercase tracking-wider">
                            <th class="px-5 py-3.5 font-medium">User</th>
                            <th class="px-5 py-3.5 font-medium">Phone</th>
                            <th class="px-5 py-3.5 font-medium">Tickets</th>
                            <th class="px-5 py-3.5 font-medium">Social</th>
                            <th class="px-5 py-3.5 font-medium">Joined</th>
                            <th class="px-5 py-3.5 font-medium"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($users as $user)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    @if($user->profile_image)
                                        <img src="{{ $user->profileImageUrl() }}"
                                             class="w-9 h-9 rounded-full object-cover ring-2 ring-slate-100"
                                             onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                                        <div class="w-9 h-9 bg-blue-100 rounded-full items-center justify-center" style="display:none">
                                            <span class="text-blue-600 font-bold text-sm">{{ strtoupper(substr($user->full_name,0,1)) }}</span>
                                        </div>
                                    @else
                                        <div class="w-9 h-9 bg-blue-100 rounded-full flex items-center justify-center">
                                            <span class="text-blue-600 font-bold text-sm">{{ strtoupper(substr($user->full_name,0,1)) }}</span>
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-medium text-slate-800">{{ $user->full_name }}</p>
                                        <p class="text-xs text-slate-400">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-slate-600">{{ $user->phone ?: '—' }}</td>
                            <td class="px-5 py-4">
                                <a href="{{ route('admin.tickets.index', ['search' => $user->email]) }}"
                                   class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 text-xs font-semibold hover:bg-blue-100 transition-colors">
                                    {{ $user->tickets_count }} ticket{{ $user->tickets_count !== 1 ? 's' : '' }}
                                </a>
                            </td>
                            <td class="px-5 py-4">
                                @php $links = $user->social_links ?? []; @endphp
                                <div class="flex items-center gap-2">
                                    @if(!empty($links['twitter']))
                                        <a href="{{ $links['twitter'] }}" target="_blank" class="text-sky-500 hover:text-sky-600" title="Twitter/X">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.73-8.835L1.254 2.25H8.08l4.26 5.632zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                                        </a>
                                    @endif
                                    @if(!empty($links['facebook']))
                                        <a href="{{ $links['facebook'] }}" target="_blank" class="text-blue-600 hover:text-blue-700" title="Facebook">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                        </a>
                                    @endif
                                    @if(!empty($links['instagram']))
                                        <a href="{{ $links['instagram'] }}" target="_blank" class="text-pink-500 hover:text-pink-600" title="Instagram">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                                        </a>
                                    @endif
                                    @if(!empty($links['linkedin']))
                                        <a href="{{ $links['linkedin'] }}" target="_blank" class="text-blue-700 hover:text-blue-800" title="LinkedIn">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                                        </a>
                                    @endif
                                    @if(empty($links))
                                        <span class="text-slate-300 text-xs">—</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4 text-slate-500 text-xs">{{ $user->created_at->format('M j, Y') }}</td>
                            <td class="px-5 py-4">
                                <a href="{{ route('admin.messages.show', $user) }}" title="Message" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-blue-50 text-blue-700 text-xs font-semibold hover:bg-blue-100 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                                    Message
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-4 border-t border-slate-100">{{ $users->withQueryString()->links() }}</div>
        @endif
    </div>

    {{-- Add User Modal --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6">
            <h2 class="font-semibold text-slate-900 mb-4">Add Ticket User</h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Full Name</label>
                    <input type="text" x-model="form.full_name" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g. John Smith">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
                    <input type="email" x-model="form.email" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="john@example.com">
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Phone <span class="text-slate-400 font-normal">(optional)</span></label>
                        <input type="text" x-model="form.phone" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Company <span class="text-slate-400 font-normal">(optional)</span></label>
                        <input type="text" x-model="form.company_name" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Password</label>
                    <input type="password" x-model="form.password" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Min. 8 characters">
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <button @click="showModal = false" class="btn-secondary cursor-pointer">Cancel</button>
                <button @click="save()" :disabled="saving" class="btn-primary cursor-pointer disabled:opacity-50">Save</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function ticketUserManager() {
    return {
        showModal: false,
        saving: false,
        form: { full_name: '', email: '', phone: '', company_name: '', password: '' },

        save() {
            this.saving = true;
            fetch('{{ route('admin.ticket-users.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify(this.form),
            })
            .then(r => r.json().then(data => ({ status: r.status, data })))
            .then(({ status, data }) => {
                this.saving = false;
                if (status === 422) {
                    alert(Object.values(data.errors).flat().join('\n'));
                    return;
                }
                location.reload();
            })
            .catch(() => { this.saving = false; });
        }
    }
}
</script>
@endpush
