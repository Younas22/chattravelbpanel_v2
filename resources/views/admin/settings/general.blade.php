@extends('admin.layouts.app')
@section('title', 'General Settings')

@section('content')
<div class="max-w-2xl space-y-6">

    <div class="flex gap-3">
        <a href="{{ route('admin.settings.widget') }}" class="btn-secondary">Widget</a>
        <span class="btn-primary">SMTP</span>
        <a href="{{ route('admin.settings.pusher') }}" class="btn-secondary">Pusher</a>
        <a href="{{ route('admin.settings.profile') }}" class="btn-secondary">Profile</a>
    </div>

    <div class="card">
        <h2 class="font-semibold text-slate-900 mb-4">Email / SMTP Settings</h2>
        <form method="POST" action="{{ route('admin.settings.general.update') }}" class="space-y-4">
            @csrf
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">SMTP Host</label>
                    <input type="text" name="smtp_host" value="{{ env('MAIL_HOST') }}"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="smtp.gmail.com">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">SMTP Port</label>
                    <input type="number" name="smtp_port" value="{{ env('MAIL_PORT', 587) }}"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Username</label>
                    <input type="text" name="smtp_username" value="{{ env('MAIL_USERNAME') }}"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Password</label>
                    <input type="password" name="smtp_password" placeholder="Leave blank to keep current"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">From Address</label>
                    <input type="email" name="smtp_from" value="{{ env('MAIL_FROM_ADDRESS') }}"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">From Name</label>
                    <input type="text" name="smtp_name" value="{{ env('MAIL_FROM_NAME') }}"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div class="flex justify-end pt-2">
                <button type="submit" class="btn-primary">Save SMTP Settings</button>
            </div>
        </form>
    </div>
</div>
@endsection
