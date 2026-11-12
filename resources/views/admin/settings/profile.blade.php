@extends('admin.layouts.app')
@section('title', 'Profile Settings')

@section('content')
@include('admin.settings._tabs')
<div class="max-w-2xl space-y-6">

    <div class="card">
        <h2 class="font-semibold text-slate-900 mb-1">Your Profile</h2>
        <p class="text-sm text-slate-500 mb-5">Your name and photo show up wherever you reply to customers. The email is what you sign in with.</p>

        <form method="POST" action="{{ route('admin.settings.profile.update') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div class="flex flex-col sm:flex-row sm:items-center gap-4 sm:gap-5">
                <img src="{{ auth()->user()->avatar_url }}" class="shrink-0 w-20 h-20 rounded-full object-cover ring-4 ring-slate-100" id="profile-img-preview">
                <div class="min-w-0">
                    <label class="block text-xs font-medium text-slate-500 mb-1.5">JPG, PNG, GIF or WebP — max 2MB</label>
                    <input type="file" name="avatar" id="profile_avatar_input" accept=".jpg,.jpeg,.png,.gif,.webp"
                        class="block w-full max-w-full text-sm text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-brand-50 file:text-brand-700 file:font-medium file:cursor-pointer hover:file:bg-brand-100">
                    @error('avatar')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Name</label>
                <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div x-data="{ email: @js(old('email', auth()->user()->email)), original: @js(auth()->user()->email) }">
                <label for="profile_email" class="block text-sm font-medium text-slate-700 mb-1.5">Email (used to sign in)</label>
                <input id="profile_email" type="email" name="email" x-model="email" required autocomplete="email"
                    class="w-full px-3.5 py-2.5 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 {{ $errors->has('email') ? 'border-red-300' : 'border-slate-200' }}">
                @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror

                {{-- Changing the sign-in email needs the current password --}}
                <div x-show="email.trim().toLowerCase() !== original.toLowerCase()" x-cloak x-transition class="mt-3 rounded-xl bg-amber-50 border border-amber-200 p-3">
                    <label for="email_password" class="block text-xs font-semibold text-amber-800 mb-1">Confirm with your current password</label>
                    <p class="text-xs text-amber-700 mb-2">You'll sign in with the new email from now on.</p>
                    <input id="email_password" type="password" name="email_password" autocomplete="current-password"
                        :required="email.trim().toLowerCase() !== original.toLowerCase()"
                        placeholder="Current password"
                        class="w-full px-3.5 py-2.5 rounded-xl border bg-white text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 {{ $errors->has('email_password') ? 'border-red-300' : 'border-amber-200' }}">
                    @error('email_password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="btn-primary cursor-pointer">Save Profile</button>
            </div>
        </form>
    </div>

    {{-- Change password --}}
    <div class="card scroll-mt-20" id="change-password" x-data="passwordForm()">
        <div class="flex items-start gap-3 mb-5">
            <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 grid place-items-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
            </div>
            <div>
                <h2 class="font-semibold text-slate-900">Change password</h2>
                <p class="text-sm text-slate-500">Use at least 8 characters. A mix of letters, numbers and symbols is strongest.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.settings.profile.password') }}" class="space-y-4" @submit="saving = true">
            @csrf

            @foreach([
                ['current_password',      'Current password',     'current-password', 'Enter your current password'],
                ['password',              'New password',         'new-password',     'At least 8 characters'],
                ['password_confirmation', 'Confirm new password', 'new-password',     'Type the new password again'],
            ] as [$field, $label, $auto, $ph])
            <div>
                <label for="{{ $field }}" class="block text-sm font-medium text-slate-700 mb-1.5">{{ $label }}</label>
                <div class="relative">
                    <input id="{{ $field }}" name="{{ $field }}" :type="show.{{ $field }} ? 'text' : 'password'" required autocomplete="{{ $auto }}"
                        @if($field === 'password') x-model="pw" @elseif($field === 'password_confirmation') x-model="pw2" @endif
                        placeholder="{{ $ph }}"
                        class="w-full pl-3.5 pr-11 py-2.5 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 {{ $errors->has($field) ? 'border-red-300' : 'border-slate-200' }}">
                    <button type="button" @click="show.{{ $field }} = !show.{{ $field }}" :aria-label="show.{{ $field }} ? 'Hide password' : 'Show password'"
                        class="absolute right-1.5 top-1/2 -translate-y-1/2 grid place-items-center w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 cursor-pointer">
                        <svg x-show="!show.{{ $field }}" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <svg x-show="show.{{ $field }}" x-cloak class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                    </button>
                </div>
                @error($field)<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror

                @if($field === 'password')
                {{-- strength meter --}}
                <div x-show="pw.length" x-cloak class="mt-2 flex items-center gap-2">
                    <div class="flex-1 grid grid-cols-4 gap-1">
                        <template x-for="i in 4" :key="i">
                            <span class="h-1.5 rounded-full transition-colors" :class="i <= strength ? strengthColor : 'bg-slate-200'"></span>
                        </template>
                    </div>
                    <span class="text-xs font-medium w-14 text-right" :class="strengthText" x-text="strengthLabel"></span>
                </div>
                @endif
                @if($field === 'password_confirmation')
                <p x-show="pw2.length && pw !== pw2" x-cloak class="text-xs text-amber-600 mt-1">Passwords don't match yet.</p>
                @endif
            </div>
            @endforeach

            <div class="flex items-center justify-between gap-3 pt-2">
                <p class="text-xs text-slate-400">You stay signed in on this device after changing it.</p>
                <button type="submit" class="btn-primary cursor-pointer disabled:opacity-60" :disabled="saving || pw.length < 8 || pw !== pw2">
                    <span x-text="saving ? 'Updating…' : 'Update password'"></span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function passwordForm() {
    return {
        show: { current_password: false, password: false, password_confirmation: false },
        pw: '', pw2: '', saving: false,
        get strength() {
            const p = this.pw; let s = 0;
            if (p.length >= 8) s++;
            if (p.length >= 12) s++;
            if (/[a-z]/.test(p) && /[A-Z]/.test(p)) s++;
            if (/\d/.test(p) && /[^A-Za-z0-9]/.test(p)) s++;
            return p.length < 8 ? Math.min(s, 1) : Math.max(s, 1);
        },
        get strengthLabel() { return ['Too short', 'Weak', 'Fair', 'Good', 'Strong'][this.pw.length < 8 ? 0 : this.strength]; },
        get strengthColor() { return ['bg-red-500', 'bg-red-500', 'bg-amber-500', 'bg-lime-500', 'bg-green-600'][this.pw.length < 8 ? 0 : this.strength]; },
        get strengthText()  { return ['text-red-600', 'text-red-600', 'text-amber-600', 'text-lime-700', 'text-green-700'][this.pw.length < 8 ? 0 : this.strength]; },
    };
}

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
