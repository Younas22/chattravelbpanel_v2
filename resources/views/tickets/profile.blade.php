@extends('tickets.layouts.app')
@section('title', 'My Profile')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <h1 class="text-xl font-bold text-slate-800">My Profile</h1>

    {{-- Profile Image --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <h2 class="text-sm font-semibold text-slate-700 mb-4">Profile Image</h2>
        <div class="flex items-center gap-5">
            <div class="shrink-0">
                @if($user->profile_image)
                    <img src="{{ $user->profileImageUrl() }}"
                         class="w-20 h-20 rounded-full object-cover ring-4 ring-slate-100"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex'"
                         id="profile-img-preview">
                    <div class="w-20 h-20 bg-blue-100 rounded-full items-center justify-center text-blue-600 font-bold text-2xl" style="display:none" id="profile-img-fallback">
                        {{ strtoupper(substr($user->full_name, 0, 1)) }}
                    </div>
                @else
                    <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 font-bold text-2xl" id="profile-img-fallback">
                        {{ strtoupper(substr($user->full_name, 0, 1)) }}
                    </div>
                @endif
            </div>
            <form method="POST" action="{{ route('tickets.profile.image') }}" enctype="multipart/form-data" class="flex flex-col gap-3">
                @csrf
                <label class="flex flex-col gap-1">
                    <span class="text-xs text-slate-500">JPG, PNG, GIF or WebP — max 2MB</span>
                    <input type="file" name="image" accept=".jpg,.jpeg,.png,.gif,.webp"
                           class="text-sm text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 file:font-medium file:cursor-pointer hover:file:bg-blue-100">
                </label>
                @error('image')<p class="text-red-500 text-xs">{{ $message }}</p>@enderror
                <button type="submit" class="self-start px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700 transition-colors cursor-pointer">Upload Image</button>
            </form>
        </div>
    </div>

    {{-- Profile Details --}}
    <form method="POST" action="{{ route('tickets.profile.update') }}" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5">
        @csrf
        <h2 class="text-sm font-semibold text-slate-700">Personal Information</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1.5">Full Name *</label>
                <input type="text" name="full_name" value="{{ old('full_name', $user->full_name) }}"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('full_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1.5">Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('phone')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1.5">Email</label>
            <input type="email" value="{{ $user->email }}" disabled
                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm bg-slate-50 text-slate-400 cursor-not-allowed">
        </div>

        <hr class="border-slate-100">
        <h2 class="text-sm font-semibold text-slate-700">Social Media Links</h2>

        @php $social = $user->social_links ?? []; @endphp

        <div class="space-y-3">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1.5">Twitter / X</label>
                <input type="url" name="social_links[twitter]" value="{{ old('social_links.twitter', $social['twitter'] ?? '') }}"
                       placeholder="https://twitter.com/username"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('social_links.twitter')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1.5">Facebook</label>
                <input type="url" name="social_links[facebook]" value="{{ old('social_links.facebook', $social['facebook'] ?? '') }}"
                       placeholder="https://facebook.com/username"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('social_links.facebook')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1.5">Instagram</label>
                <input type="url" name="social_links[instagram]" value="{{ old('social_links.instagram', $social['instagram'] ?? '') }}"
                       placeholder="https://instagram.com/username"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('social_links.instagram')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1.5">LinkedIn</label>
                <input type="url" name="social_links[linkedin]" value="{{ old('social_links.linkedin', $social['linkedin'] ?? '') }}"
                       placeholder="https://linkedin.com/in/username"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('social_links.linkedin')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <button type="submit" class="w-full py-2.5 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition-colors cursor-pointer">
            Save Profile
        </button>
    </form>

    {{-- Change Password --}}
    <form method="POST" action="{{ route('tickets.profile.password') }}" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
        @csrf
        <h2 class="text-sm font-semibold text-slate-700">Change Password</h2>

        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1.5">Current Password</label>
            <input type="password" name="current_password"
                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('current_password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1.5">New Password</label>
                <input type="password" name="password"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1.5">Confirm New Password</label>
                <input type="password" name="password_confirmation"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <button type="submit" class="w-full py-2.5 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition-colors cursor-pointer">
            Update Password
        </button>
    </form>

</div>
@endsection
