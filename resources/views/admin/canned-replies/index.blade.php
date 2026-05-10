@extends('admin.layouts.app')
@section('title', 'Canned Replies')

@section('content')
<div class="space-y-4" x-data="cannedManager()">

    <div class="flex items-center justify-between">
        <p class="text-sm text-slate-500">Pre-written responses to common questions. Type <kbd class="px-1.5 py-0.5 text-xs bg-slate-100 rounded-lg border border-slate-200">/</kbd> in chat to search.</p>
        <button @click="openCreate()" class="btn-primary cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Reply
        </button>
    </div>

    <div class="card !p-0 overflow-hidden">
        @if($replies->isEmpty())
            <div class="py-16 text-center">
                <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h7"/></svg>
                <p class="text-slate-500">No canned replies yet.</p>
            </div>
        @else
            <div class="divide-y divide-slate-50">
                @foreach($replies as $reply)
                <div class="flex items-start gap-4 px-5 py-4 hover:bg-slate-50 transition-colors group">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <p class="font-semibold text-slate-900 text-sm">{{ $reply->title }}</p>
                            @if($reply->shortcut)
                                <span class="badge bg-slate-100 text-slate-600 font-mono">/{{ $reply->shortcut }}</span>
                            @endif
                        </div>
                        <p class="text-sm text-slate-500 line-clamp-2">{{ $reply->body }}</p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button @click="openEdit({{ $reply->toJson() }})" class="btn-secondary !py-1.5 !text-xs cursor-pointer">Edit</button>
                        <button @click="deleteReply({{ $reply->id }})" class="btn-danger !py-1.5 !text-xs cursor-pointer">Delete</button>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Modal --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6">
            <h2 class="font-semibold text-slate-900 mb-4" x-text="editId ? 'Edit Reply' : 'New Canned Reply'"></h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Title</label>
                    <input type="text" x-model="form.title" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g. Greeting">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Message</label>
                    <textarea x-model="form.body" rows="4" class="w-full resize-none px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="The reply message…"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Shortcut <span class="text-slate-400 font-normal">(optional)</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">/</span>
                        <input type="text" x-model="form.shortcut" class="w-full pl-6 pr-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="greeting">
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <button @click="showModal = false" class="btn-secondary cursor-pointer">Cancel</button>
                <button @click="save()" class="btn-primary cursor-pointer">Save</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function cannedManager() {
    return {
        showModal: false,
        editId: null,
        form: { title: '', body: '', shortcut: '' },

        openCreate() { this.editId = null; this.form = { title: '', body: '', shortcut: '' }; this.showModal = true; },
        openEdit(r) { this.editId = r.id; this.form = { title: r.title, body: r.body, shortcut: r.shortcut || '' }; this.showModal = true; },

        save() {
            const url = this.editId ? `/admin/canned-replies/${this.editId}` : '/admin/canned-replies';
            const method = this.editId ? 'PUT' : 'POST';
            fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify(this.form)
            }).then(() => location.reload());
        },

        deleteReply(id) {
            if (!confirm('Delete this reply?')) return;
            fetch(`/admin/canned-replies/${id}`, {
                method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            }).then(() => location.reload());
        }
    }
}
</script>
@endpush
