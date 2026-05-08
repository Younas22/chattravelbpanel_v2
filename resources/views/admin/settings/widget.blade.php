@extends('admin.layouts.app')
@section('title', 'Widget Settings')

@section('content')
<div class="grid lg:grid-cols-3 gap-6">

    {{-- Settings Form --}}
    <div class="lg:col-span-2 space-y-4">

        <form method="POST" action="{{ route('admin.settings.widget.update') }}" class="space-y-4">
            @csrf

            <div class="card">
                <h2 class="font-semibold text-slate-900 mb-4">Appearance</h2>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Primary Color</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="primary_color" value="{{ $settings['primary_color'] }}"
                                class="w-10 h-10 rounded-lg border border-slate-200 cursor-pointer p-1">
                            <input type="text" id="primary_color_text" value="{{ $settings['primary_color'] }}"
                                class="flex-1 px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Text Color</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="text_color" value="{{ $settings['text_color'] }}"
                                class="w-10 h-10 rounded-lg border border-slate-200 cursor-pointer p-1">
                            <input type="text" value="{{ $settings['text_color'] }}"
                                class="flex-1 px-3 py-2 rounded-xl border border-slate-200 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Position</label>
                        <select name="position" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="bottom-right" {{ $settings['position'] === 'bottom-right' ? 'selected' : '' }}>Bottom Right</option>
                            <option value="bottom-left" {{ $settings['position'] === 'bottom-left' ? 'selected' : '' }}>Bottom Left</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Border Radius (px)</label>
                        <input type="number" name="border_radius" value="{{ $settings['border_radius'] }}" min="0" max="50"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="flex items-center gap-6 mt-4">
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="checkbox" name="dark_mode" {{ $settings['dark_mode'] === 'true' ? 'checked' : '' }} class="rounded border-slate-300 text-blue-600">
                        <span class="text-slate-700">Dark Mode</span>
                    </label>
                </div>
            </div>

            <div class="card">
                <h2 class="font-semibold text-slate-900 mb-4">Content</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Widget Title</label>
                        <input type="text" name="widget_title" value="{{ $settings['widget_title'] }}"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Subtitle</label>
                        <input type="text" name="widget_subtitle" value="{{ $settings['widget_subtitle'] }}"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Agent / Team Name</label>
                        <input type="text" name="agent_name" value="{{ $settings['agent_name'] }}"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Welcome Message (when online)</label>
                        <textarea name="welcome_message" rows="2"
                            class="w-full resize-none px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ $settings['welcome_message'] }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Offline Message</label>
                        <textarea name="offline_message" rows="2"
                            class="w-full resize-none px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ $settings['offline_message'] }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card">
                <h2 class="font-semibold text-slate-900 mb-4">Behavior</h2>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Auto Popup Delay (seconds)</label>
                        <input type="number" name="popup_delay" value="{{ $settings['popup_delay'] }}" min="0" max="60"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-6 mt-4">
                    @foreach([
                        ['auto_popup', 'Auto Popup'],
                        ['sound_enabled', 'Sound Notifications'],
                        ['show_online_status', 'Show Online Status'],
                        ['show_branding', 'Show Branding'],
                    ] as [$key, $label])
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="checkbox" name="{{ $key }}" {{ $settings[$key] === 'true' ? 'checked' : '' }} class="rounded border-slate-300 text-blue-600">
                        <span class="text-slate-700">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="btn-primary">Save Widget Settings</button>
            </div>
        </form>

        {{-- Embed Code --}}
        <div class="card">
            <h2 class="font-semibold text-slate-900 mb-3">Embed Code</h2>
            <p class="text-sm text-slate-500 mb-3">Add this script to your website's <code class="bg-slate-100 px-1 rounded">&lt;/body&gt;</code> tag:</p>
            <div class="relative">
                <pre class="bg-slate-900 text-green-400 text-xs p-4 rounded-xl leading-relaxed" style="white-space:pre-wrap;word-break:break-all;overflow-wrap:break-word;"><code>&lt;script src="{{ url('/widget.js') }}"&gt;&lt;/script&gt;</code></pre>
                <button onclick="navigator.clipboard.writeText(this.previousElementSibling.textContent.trim()); this.textContent='Copied!'; setTimeout(()=>this.textContent='Copy',2000)"
                    class="absolute top-3 right-3 text-xs bg-slate-700 hover:bg-slate-600 text-white px-2.5 py-1 rounded-lg transition-colors">Copy</button>
            </div>
        </div>
    </div>

    {{-- Live Preview --}}
    <div class="hidden lg:block">
        <div class="sticky top-24">
            <p class="text-sm font-medium text-slate-700 mb-3">Live Preview</p>
            <div class="bg-slate-200 rounded-2xl h-[500px] relative overflow-hidden">
                <div class="absolute bottom-4 right-4 w-14 h-14 rounded-full flex items-center justify-center shadow-lg" style="background: {{ $settings['primary_color'] }}">
                    <svg class="w-6 h-6" fill="none" stroke="{{ $settings['text_color'] }}" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                </div>
                <div class="absolute bottom-20 right-4 w-52 rounded-2xl shadow-xl overflow-hidden border border-white/20" style="border-radius: {{ $settings['border_radius'] }}px">
                    <div class="p-3" style="background: {{ $settings['primary_color'] }}">
                        <p class="text-xs font-bold" style="color: {{ $settings['text_color'] }}">{{ $settings['widget_title'] }}</p>
                        <p class="text-[10px] opacity-80 mt-0.5" style="color: {{ $settings['text_color'] }}">{{ $settings['widget_subtitle'] }}</p>
                    </div>
                    <div class="bg-white p-3">
                        <div class="bg-slate-100 rounded-xl rounded-tl-none px-3 py-2 text-xs text-slate-700">{{ $settings['welcome_message'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="flex items-center gap-4 mt-4">
    <a href="{{ route('admin.settings.general') }}" class="btn-secondary">SMTP Settings</a>
    <a href="{{ route('admin.settings.pusher') }}" class="btn-secondary">Pusher Settings</a>
</div>
@endsection
