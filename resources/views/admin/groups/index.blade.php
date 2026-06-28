@extends('admin.layouts.app')
@section('title', 'Groups')

@section('content')
<div class="space-y-4" x-data="groupManager()">

    <div class="card !p-4 flex flex-wrap items-center gap-3">
        <form method="GET" class="flex flex-wrap gap-3 flex-1">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search groups…"
                class="flex-1 min-w-[200px] px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit" class="btn-primary cursor-pointer">Search</button>
        </form>
        <div class="text-sm text-slate-500">Total: <span class="font-semibold text-slate-800">{{ $groups->total() }}</span> groups</div>
        <button @click="openCreate()" class="btn-primary cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Create Group
        </button>
    </div>

    <div class="card !p-0 overflow-hidden">
        @if($groups->isEmpty())
            <div class="py-20 text-center">
                <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <p class="text-slate-500 font-medium">No groups yet</p>
            </div>
        @else
            <div class="divide-y divide-slate-50">
                @foreach($groups as $group)
                <a href="{{ route('admin.groups.show', $group) }}" class="flex items-center gap-4 px-5 py-4 hover:bg-slate-50 transition-colors">
                    <div class="relative shrink-0">
                        <div class="w-10 h-10 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center font-semibold">
                            {{ strtoupper(substr($group->name, 0, 1)) }}
                        </div>
                        @if($group->unread_admin > 0)
                            <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-[9px] rounded-full flex items-center justify-center font-bold">{{ $group->unread_admin > 9 ? '9+' : $group->unread_admin }}</span>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-semibold text-slate-900 text-sm truncate">{{ $group->name }}</p>
                            <span class="text-xs text-slate-400 shrink-0">{{ $group->updated_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-xs text-slate-500 truncate">{{ $group->latestMessage?->body ?: ($group->description ?: 'No messages yet') }}</p>
                    </div>
                    <span class="text-xs font-medium text-slate-500 shrink-0">{{ $group->members_count }} member{{ $group->members_count !== 1 ? 's' : '' }}</span>
                </a>
                @endforeach
            </div>
            <div class="px-5 py-4 border-t border-slate-100">{{ $groups->withQueryString()->links() }}</div>
        @endif
    </div>

    {{-- Create Group Modal --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6">
            <h2 class="font-semibold text-slate-900 mb-4">Create Group</h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Group Name</label>
                    <input type="text" x-model="form.name" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g. VIP Clients">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Description <span class="text-slate-400 font-normal">(optional)</span></label>
                    <textarea x-model="form.description" rows="2" class="w-full resize-none px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Members <span class="text-slate-400 font-normal">(optional, can add later)</span></label>
                    <input type="text" x-model="memberSearch" placeholder="Filter users…"
                        class="w-full mb-2 px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <div class="max-h-48 overflow-y-auto border border-slate-200 rounded-xl divide-y divide-slate-50">
                        @forelse($allUsers as $user)
                        <label class="flex items-center gap-3 px-3 py-2 hover:bg-slate-50 cursor-pointer"
                            x-show="'{{ strtolower($user->full_name . ' ' . $user->email) }}'.includes(memberSearch.toLowerCase())">
                            <input type="checkbox" value="{{ $user->id }}" x-model="form.member_ids" class="rounded border-slate-300">
                            <div class="min-w-0">
                                <p class="text-sm text-slate-800 truncate">{{ $user->full_name }}</p>
                                <p class="text-xs text-slate-400 truncate">{{ $user->email }}</p>
                            </div>
                        </label>
                        @empty
                        <p class="px-3 py-3 text-sm text-slate-400 text-center">No ticket users yet</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <button @click="showModal = false" class="btn-secondary cursor-pointer">Cancel</button>
                <button @click="save()" :disabled="saving" class="btn-primary cursor-pointer disabled:opacity-50">Create</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function groupManager() {
    return {
        showModal: false,
        saving: false,
        memberSearch: '',
        form: { name: '', description: '', member_ids: [] },

        openCreate() {
            this.form = { name: '', description: '', member_ids: [] };
            this.memberSearch = '';
            this.showModal = true;
        },

        save() {
            this.saving = true;
            fetch('{{ route('admin.groups.store') }}', {
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
                window.location = data.redirect_url;
            })
            .catch(() => { this.saving = false; });
        }
    }
}
</script>
@endpush
