@extends('admin.layouts.app')
@section('title', 'Quick FAQs')

@section('content')
<div class="space-y-4" x-data="faqManager()">

    <div class="flex items-center justify-between">
        <p class="text-sm text-slate-500">Manage the quick questions shown in the chat widget before starting a conversation.</p>
        <button @click="openCreate()" class="btn-primary cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add FAQ
        </button>
    </div>

    <div class="card !p-0 overflow-hidden">
        @if($faqs->isEmpty())
            <div class="py-16 text-center">
                <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-slate-500">No FAQs yet. Add your first one!</p>
            </div>
        @else
            <div class="divide-y divide-slate-50">
                @foreach($faqs as $faq)
                <div class="flex items-start gap-4 px-5 py-4 hover:bg-slate-50 transition-colors">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <p class="font-semibold text-slate-900 text-sm">{{ $faq->question }}</p>
                            @if(!$faq->is_active)
                                <span class="badge bg-slate-100 text-slate-500">Inactive</span>
                            @endif
                            @if($faq->show_chat_button)
                                <span class="badge bg-blue-100 text-blue-700">Show Chat Button</span>
                            @endif
                        </div>
                        <p class="text-sm text-slate-500 line-clamp-2">{{ $faq->answer }}</p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <button @click="openEdit({{ $faq->toJson() }})" class="btn-secondary !py-1.5 !text-xs cursor-pointer">Edit</button>
                        <button @click="deleteFaq({{ $faq->id }})" class="btn-danger !py-1.5 !text-xs cursor-pointer">Delete</button>
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
            <h2 class="font-semibold text-slate-900 mb-4" x-text="editId ? 'Edit FAQ' : 'Add FAQ'"></h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Question</label>
                    <input type="text" x-model="form.question" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g. What is the pricing?">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Answer</label>
                    <textarea x-model="form.answer" rows="4" class="w-full resize-none px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="The answer to this question…"></textarea>
                </div>
                <div class="flex items-center gap-6">
                    <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                        <input type="checkbox" x-model="form.is_active" class="rounded border-slate-300 text-blue-600">
                        Active
                    </label>
                    <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                        <input type="checkbox" x-model="form.show_chat_button" class="rounded border-slate-300 text-blue-600">
                        Show "Chat with us" button
                    </label>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <button @click="showModal = false" class="btn-secondary cursor-pointer">Cancel</button>
                <button @click="save()" class="btn-primary cursor-pointer">Save FAQ</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function faqManager() {
    return {
        showModal: false,
        editId: null,
        form: { question: '', answer: '', is_active: true, show_chat_button: true, sort_order: 0 },

        openCreate() {
            this.editId = null;
            this.form = { question: '', answer: '', is_active: true, show_chat_button: true, sort_order: 0 };
            this.showModal = true;
        },

        openEdit(faq) {
            this.editId = faq.id;
            this.form = { question: faq.question, answer: faq.answer, is_active: faq.is_active, show_chat_button: faq.show_chat_button, sort_order: faq.sort_order };
            this.showModal = true;
        },

        save() {
            const url = this.editId ? `/admin/faqs/${this.editId}` : '/admin/faqs';
            const method = this.editId ? 'PUT' : 'POST';
            fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify(this.form)
            }).then(r => r.json()).then(() => location.reload());
        },

        deleteFaq(id) {
            if (!confirm('Delete this FAQ?')) return;
            fetch(`/admin/faqs/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            }).then(() => location.reload());
        }
    }
}
</script>
@endpush
