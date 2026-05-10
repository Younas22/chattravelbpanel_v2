@extends('tickets.layouts.app')
@section('title', 'New Ticket')

@section('content')
<div class="max-w-2xl">
    <div class="mb-6">
        <h1 class="text-xl font-bold text-slate-900">Create Support Ticket</h1>
        <p class="text-sm text-slate-500 mt-1">Describe your issue and we'll get back to you as soon as possible.</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        @if($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-100 text-red-600 text-sm rounded-xl space-y-1">
                @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Subject <span class="text-red-500">*</span></label>
                <input type="text" name="subject" value="{{ old('subject') }}" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Brief description of your issue">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Priority</label>
                <select name="priority" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="low">Low</option>
                    <option value="medium" selected>Medium</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Description <span class="text-red-500">*</span></label>
                <textarea name="description" rows="6" required
                    class="w-full resize-none px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Please describe your issue in detail…">{{ old('description') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Attachment <span class="text-slate-400 font-normal">(optional)</span></label>
                <input type="file" name="attachment" accept=".jpg,.jpeg,.png,.gif,.pdf,.zip,.txt"
                    class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-blue-50 file:text-blue-700 file:font-medium hover:file:bg-blue-100 transition-colors">
            </div>

            <div class="flex items-center justify-between pt-2">
                <a href="{{ route('tickets.index') }}" class="text-sm text-slate-500 hover:text-slate-700">← Back</a>
                <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition-colors text-sm cursor-pointer">Submit Ticket</button>
            </div>
        </form>
    </div>
</div>
@endsection
