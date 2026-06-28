@extends('admin.layouts.app')
@section('title', 'Pusher Settings')

@section('content')
<div class="max-w-2xl space-y-6">

    <div class="flex gap-3">
        <a href="{{ route('admin.settings.widget') }}" class="btn-secondary">Widget</a>
        <a href="{{ route('admin.settings.general') }}" class="btn-secondary">SMTP</a>
        <span class="btn-primary">Pusher</span>
        <a href="{{ route('admin.settings.profile') }}" class="btn-secondary">Profile</a>
    </div>

    <div class="card">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h2 class="font-semibold text-slate-900">Pusher / Realtime Settings</h2>
                <p class="text-sm text-slate-500 mt-1">Configure Pusher for realtime messaging. Get keys at <a href="https://pusher.com" class="text-blue-600 hover:underline" target="_blank">pusher.com</a></p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.settings.pusher.update') }}" class="space-y-4">
            @csrf
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">App ID</label>
                    <input type="text" name="pusher_app_id" value="{{ env('PUSHER_APP_ID') }}"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">App Key</label>
                    <input type="text" name="pusher_app_key" value="{{ env('PUSHER_APP_KEY') }}"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">App Secret</label>
                    <input type="password" name="pusher_app_secret" value="{{ env('PUSHER_APP_SECRET') }}"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Cluster</label>
                    <input type="text" name="pusher_app_cluster" value="{{ env('PUSHER_APP_CLUSTER', 'mt1') }}"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="mt1">
                </div>
            </div>

            <div class="p-4 bg-blue-50 rounded-xl text-sm text-blue-700 border border-blue-100">
                <p class="font-medium mb-1">No Pusher? Use AJAX Polling</p>
                <p class="text-blue-600 text-xs">If Pusher keys are not set, the widget will automatically fall back to AJAX polling every 3 seconds. This works on all shared hosting.</p>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="btn-primary">Save Pusher Settings</button>
            </div>
        </form>
    </div>
</div>
@endsection
