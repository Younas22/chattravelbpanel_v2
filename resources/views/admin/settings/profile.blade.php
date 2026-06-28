@extends('admin.layouts.app')
@section('title', 'Profile Settings')

@section('content')
<div class="max-w-2xl space-y-6">

    <div class="flex gap-3">
        <a href="{{ route('admin.settings.widget') }}" class="btn-secondary">Widget</a>
        <a href="{{ route('admin.settings.general') }}" class="btn-secondary">SMTP</a>
        <a href="{{ route('admin.settings.pusher') }}" class="btn-secondary">Pusher</a>
        <span class="btn-primary">Profile</span>
    </div>

    <div class="card">
        <h2 class="font-semibold text-slate-900 mb-4">Your Profile</h2>
        <p class="text-sm text-slate-500 mb-5">This name and photo show up wherever you reply to customers in chat.</p>

        <form method="POST" action="{{ route('admin.settings.profile.update') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div class="flex items-center gap-5">
                <img src="{{ auth()->user()->avatar_url }}" class="w-20 h-20 rounded-full object-cover ring-4 ring-slate-100" id="profile-img-preview">
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1.5">JPG, PNG, GIF or WebP — max 2MB</label>
                    <input type="file" name="avatar" id="profile_avatar_input" accept=".jpg,.jpeg,.png,.gif,.webp"
                        class="text-sm text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 file:font-medium file:cursor-pointer hover:file:bg-blue-100">
                    @error('avatar')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Name</label>
                <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="btn-primary cursor-pointer">Save Profile</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const input = document.getElementById('profile_avatar_input');
    const preview = document.getElementById('profile-img-preview');
    if (!input || !preview) return;
    input.addEventListener('change', () => {
        const file = input.files[0];
        if (!file) return;
        preview.src = URL.createObjectURL(file);
    });
})();
</script>
@endpush
