@extends('admin.layouts.app')
@section('title', 'Widget Settings')

@section('content')
<div class="grid lg:grid-cols-3 gap-6">

    {{-- Settings Form --}}
    <div class="lg:col-span-2 space-y-4">

        <form method="POST" action="{{ route('admin.settings.widget.update') }}" enctype="multipart/form-data" class="space-y-4" id="widget-form">
            @csrf

            {{-- Branding --}}
            <div class="card">
                <h2 class="font-semibold text-slate-900 mb-4">System Branding</h2>
                <p class="text-xs text-slate-500 mb-4">These settings affect the sidebar logo/name and login page.</p>
                <div class="grid sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">System Name</label>
                        <input type="text" name="system_name" id="system_name" value="{{ $settings['system_name'] }}"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">System Logo</label>
                        <div class="flex items-center gap-3">
                            @if($settings['system_logo'])
                                <img src="{{ url($settings['system_logo']) }}" class="w-10 h-10 rounded-xl object-cover border border-slate-200" id="system_logo_preview">
                                <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center shrink-0 hidden" id="system_logo_placeholder">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                                </div>
                            @else
                                <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center shrink-0" id="system_logo_placeholder">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                                </div>
                                <img src="" class="w-10 h-10 rounded-xl object-cover border border-slate-200 hidden" id="system_logo_preview">
                            @endif
                            <label class="cursor-pointer flex-1 px-3 py-2 rounded-xl border border-dashed border-slate-300 text-xs text-slate-500 hover:bg-slate-50 text-center transition-colors">
                                <span>Click to upload</span>
                                <input type="file" name="system_logo" accept="image/*" class="hidden" id="system_logo_input">
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Widget Avatar / Company Image</label>
                        <p class="text-xs text-slate-400 mb-2">Shown in the chat widget header instead of initials.</p>
                        <div class="flex items-center gap-3">
                            @if($settings['company_image'])
                                <img src="{{ url($settings['company_image']) }}" class="w-10 h-10 rounded-full object-cover border border-slate-200" id="company_image_preview">
                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center shrink-0 text-blue-600 font-bold text-sm hidden" id="company_image_placeholder">
                                    {{ strtoupper(substr($settings['agent_name'], 0, 1)) }}
                                </div>
                            @else
                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center shrink-0 text-blue-600 font-bold text-sm" id="company_image_placeholder">
                                    {{ strtoupper(substr($settings['agent_name'], 0, 1)) }}
                                </div>
                                <img src="" class="w-10 h-10 rounded-full object-cover border border-slate-200 hidden" id="company_image_preview">
                            @endif
                            <label class="cursor-pointer flex-1 px-3 py-2 rounded-xl border border-dashed border-slate-300 text-xs text-slate-500 hover:bg-slate-50 text-center transition-colors">
                                <span>Click to upload</span>
                                <input type="file" name="company_image" accept="image/*" class="hidden" id="company_image_input">
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Favicon</label>
                        <p class="text-xs text-slate-400 mb-2">Browser tab icon. Supports PNG, ICO, SVG (max 512 KB).</p>
                        <div class="flex items-center gap-3">
                            @if($settings['favicon'])
                                <img src="{{ url($settings['favicon']) }}" class="w-10 h-10 rounded-lg object-contain border border-slate-200 p-1 bg-slate-50" id="favicon_preview">
                                <div class="w-10 h-10 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center shrink-0 hidden" id="favicon_placeholder">
                                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            @else
                                <div class="w-10 h-10 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center shrink-0" id="favicon_placeholder">
                                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                                <img src="" class="w-10 h-10 rounded-lg object-contain border border-slate-200 p-1 bg-slate-50 hidden" id="favicon_preview">
                            @endif
                            <label class="cursor-pointer flex-1 px-3 py-2 rounded-xl border border-dashed border-slate-300 text-xs text-slate-500 hover:bg-slate-50 text-center transition-colors">
                                <span>Click to upload</span>
                                <input type="file" name="favicon" accept=".ico,.png,.jpg,.jpeg,.svg,.gif" class="hidden" id="favicon_input">
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Appearance --}}
            <div class="card">
                <h2 class="font-semibold text-slate-900 mb-4">Appearance</h2>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Primary Color</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="primary_color" id="primary_color" value="{{ $settings['primary_color'] }}"
                                class="w-10 h-10 rounded-lg border border-slate-200 cursor-pointer p-1">
                            <input type="text" id="primary_color_text" value="{{ $settings['primary_color'] }}"
                                class="flex-1 px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Text Color</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="text_color" id="text_color" value="{{ $settings['text_color'] }}"
                                class="w-10 h-10 rounded-lg border border-slate-200 cursor-pointer p-1">
                            <input type="text" id="text_color_text" value="{{ $settings['text_color'] }}"
                                class="flex-1 px-3 py-2 rounded-xl border border-slate-200 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Position</label>
                        <select name="position" id="position" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                            <option value="bottom-right" {{ $settings['position'] === 'bottom-right' ? 'selected' : '' }}>Bottom Right</option>
                            <option value="bottom-left" {{ $settings['position'] === 'bottom-left' ? 'selected' : '' }}>Bottom Left</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Border Radius (px)</label>
                        <input type="number" name="border_radius" id="border_radius" value="{{ $settings['border_radius'] }}" min="0" max="50"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="flex items-center gap-6 mt-4">
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="checkbox" name="dark_mode" id="dark_mode" {{ $settings['dark_mode'] === 'true' ? 'checked' : '' }} class="rounded border-slate-300 text-blue-600">
                        <span class="text-slate-700">Dark Mode</span>
                    </label>
                </div>
            </div>

            {{-- Content --}}
            <div class="card">
                <h2 class="font-semibold text-slate-900 mb-4">Content</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Widget Title</label>
                        <input type="text" name="widget_title" id="widget_title" value="{{ $settings['widget_title'] }}"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Subtitle</label>
                        <input type="text" name="widget_subtitle" id="widget_subtitle" value="{{ $settings['widget_subtitle'] }}"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Agent / Team Name</label>
                        <input type="text" name="agent_name" id="agent_name" value="{{ $settings['agent_name'] }}"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Welcome Message (when online)</label>
                        <textarea name="welcome_message" id="welcome_message" rows="2"
                            class="w-full resize-none px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ $settings['welcome_message'] }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Offline Message</label>
                        <textarea name="offline_message" rows="2"
                            class="w-full resize-none px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ $settings['offline_message'] }}</textarea>
                    </div>
                </div>
            </div>

            {{-- WhatsApp Contacts --}}
            <div class="card">
                <h2 class="font-semibold text-slate-900 mb-1">WhatsApp Contacts</h2>
                <p class="text-xs text-slate-500 mb-4">These contacts appear after the welcome message on the home screen and inside the chat. You can add multiple numbers.</p>

                <div id="wa-contacts-list" class="space-y-3 mb-4">
                    @php
                        $waContacts = json_decode($settings['whatsapp_contacts'] ?? '[]', true) ?: [];
                    @endphp
                    @forelse($waContacts as $i => $contact)
                    <div class="wa-contact-row flex items-center gap-2">
                        <input type="text" name="whatsapp_contacts[{{ $i }}][label]"
                            value="{{ $contact['label'] ?? '' }}"
                            placeholder="Label (e.g. Sales, Support)"
                            class="w-40 flex-shrink-0 px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                        <span class="text-slate-400 text-sm flex-shrink-0">wa.me/</span>
                        <input type="text" name="whatsapp_contacts[{{ $i }}][number]"
                            value="{{ $contact['number'] ?? '' }}"
                            placeholder="923207560200"
                            class="flex-1 px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                        <button type="button" onclick="this.closest('.wa-contact-row').remove()"
                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-50 text-red-400 hover:bg-red-100 hover:text-red-600 transition-colors flex-shrink-0 cursor-pointer">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    @empty
                    {{-- empty — JS will add rows --}}
                    @endforelse
                </div>

                <button type="button" id="wa-add-btn"
                    class="flex items-center gap-2 px-4 py-2 rounded-xl border border-dashed border-green-400 text-green-600 text-sm font-medium hover:bg-green-50 transition-colors cursor-pointer">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add WhatsApp Number
                </button>

                <p class="text-xs text-slate-400 mt-3">
                    Enter the number with country code, digits only (e.g. <code class="bg-slate-100 px-1 rounded">923207560200</code>). Every click is tracked automatically.
                </p>
            </div>

            {{-- Behavior --}}
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
                        ['auto_popup', 'Auto Open on Page Load'],
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
                <button type="submit" class="btn-primary cursor-pointer">Save Widget Settings</button>
            </div>
        </form>

        {{-- Embed Code --}}
        <div class="card">
            <h2 class="font-semibold text-slate-900 mb-3">Embed Code</h2>
            <p class="text-sm text-slate-500 mb-3">Add this script to your website's <code class="bg-slate-100 px-1 rounded">&lt;/body&gt;</code> tag:</p>
            <div class="relative">
                <pre class="bg-slate-900 text-green-400 text-xs p-4 rounded-xl leading-relaxed" style="white-space:pre-wrap;word-break:break-all;overflow-wrap:break-word;"><code>&lt;script src="{{ url('/widget.js') }}"&gt;&lt;/script&gt;</code></pre>
                <button onclick="navigator.clipboard.writeText(this.previousElementSibling.textContent.trim()); this.textContent='Copied!'; setTimeout(()=>this.textContent='Copy',2000)"
                    class="absolute top-3 right-3 text-xs bg-slate-700 hover:bg-slate-600 text-white px-2.5 py-1 rounded-lg transition-colors cursor-pointer">Copy</button>
            </div>
        </div>
    </div>

    {{-- Live Preview — all inline styles to avoid Tailwind JIT issues --}}
    <div style="display:none" id="preview-col">
        <div style="position:sticky;top:96px;">
            <p style="font-size:14px;font-weight:500;color:#334155;margin-bottom:12px;">Live Preview</p>
            <div id="preview-bg" style="background:#e2e8f0;border-radius:16px;height:520px;position:relative;overflow:hidden;">

                {{-- Browser bar --}}
                <div style="display:flex;align-items:center;gap:6px;padding:8px 12px;background:rgba(255,255,255,0.6);border-bottom:1px solid rgba(255,255,255,0.3);">
                    <div style="width:10px;height:10px;border-radius:50%;background:#f87171;"></div>
                    <div style="width:10px;height:10px;border-radius:50%;background:#fbbf24;"></div>
                    <div style="width:10px;height:10px;border-radius:50%;background:#4ade80;"></div>
                    <div style="flex:1;margin:0 8px;height:16px;background:rgba(255,255,255,0.5);border-radius:4px;font-size:9px;color:#94a3b8;display:flex;align-items:center;padding:0 8px;">yourwebsite.com</div>
                </div>

                {{-- Widget Launcher Button --}}
                <div id="preview-btn" style="position:absolute;bottom:16px;right:16px;width:56px;height:56px;border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 8px 24px rgba(0,0,0,0.2);cursor:pointer;background:{{ $settings['primary_color'] }};">
                    <svg width="24" height="24" fill="none" stroke="{{ $settings['text_color'] }}" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                </div>

                {{-- Widget Box --}}
                <div id="preview-box" style="position:absolute;bottom:84px;right:16px;width:224px;border-radius:{{ $settings['border_radius'] }}px;box-shadow:0 12px 40px rgba(0,0,0,0.15);overflow:hidden;">
                    {{-- Header --}}
                    <div id="preview-header" style="padding:12px;background:{{ $settings['primary_color'] }};">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div id="preview-avatar" style="width:34px;height:34px;border-radius:50%;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;flex-shrink:0;overflow:hidden;">
                                @if($settings['company_image'])
                                    <img src="{{ url($settings['company_image']) }}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                                @else
                                    <span id="preview-avatar-text" style="color:{{ $settings['text_color'] }}">{{ strtoupper(substr($settings['agent_name'], 0, 1)) }}</span>
                                @endif
                            </div>
                            <div style="flex:1;min-width:0;">
                                <div id="preview-title" style="font-size:13px;font-weight:600;color:{{ $settings['text_color'] }};white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $settings['widget_title'] }}</div>
                                <div id="preview-subtitle" style="font-size:10px;opacity:0.8;color:{{ $settings['text_color'] }};margin-top:1px;">{{ $settings['widget_subtitle'] }}</div>
                            </div>
                        </div>
                    </div>
                    {{-- Body --}}
                    <div id="preview-body" style="background:#fff;padding:12px;">
                        <div id="preview-welcome" style="background:#f1f5f9;border-radius:12px;border-top-left-radius:3px;padding:8px 12px;font-size:12px;color:#374151;line-height:1.5;">{{ $settings['welcome_message'] }}</div>
                        <div style="margin-top:8px;display:flex;">
                            <div style="flex:1;background:#f1f5f9;border-radius:20px;padding:6px 12px;font-size:10px;color:#94a3b8;">Type a message…</div>
                        </div>
                    </div>
                </div>
            </div>
            <p style="font-size:11px;color:#94a3b8;text-align:center;margin-top:8px;">Updates as you type</p>
        </div>
    </div>
</div>

<div class="flex items-center gap-4 mt-4">
    <a href="{{ route('admin.settings.general') }}" class="btn-secondary cursor-pointer">SMTP Settings</a>
    <a href="{{ route('admin.settings.pusher') }}" class="btn-secondary cursor-pointer">Pusher Settings</a>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const $ = id => document.getElementById(id);

    // Show preview column on lg+ screens
    const col = $('preview-col');
    if (col) {
        if (window.innerWidth >= 1024) col.style.display = 'block';
        window.addEventListener('resize', () => {
            col.style.display = window.innerWidth >= 1024 ? 'block' : 'none';
        });
    }

    // Color pickers sync
    const pc = $('primary_color'), pct = $('primary_color_text');
    const tc = $('text_color'), tct = $('text_color_text');
    if (pc && pct) {
        pc.addEventListener('input', () => { pct.value = pc.value; updatePreview(); });
        pct.addEventListener('input', () => { if (/^#[0-9a-f]{6}$/i.test(pct.value)) { pc.value = pct.value; updatePreview(); } });
    }
    if (tc && tct) {
        tc.addEventListener('input', () => { tct.value = tc.value; updatePreview(); });
        tct.addEventListener('input', () => { if (/^#[0-9a-f]{6}$/i.test(tct.value)) { tc.value = tct.value; updatePreview(); } });
    }

    function updatePreview() {
        const primary   = $('primary_color') ? $('primary_color').value : '{{ $settings['primary_color'] }}';
        const text      = $('text_color')    ? $('text_color').value    : '{{ $settings['text_color'] }}';
        const radius    = parseInt($('border_radius') ? $('border_radius').value : '{{ $settings['border_radius'] }}') || 16;
        const title     = $('widget_title')    ? $('widget_title').value    : '';
        const subtitle  = $('widget_subtitle') ? $('widget_subtitle').value : '';
        const welcome   = $('welcome_message') ? $('welcome_message').value : '';
        const pos       = $('position')        ? $('position').value        : 'bottom-right';
        const dark      = $('dark_mode')       ? $('dark_mode').checked     : false;

        const btn    = $('preview-btn');
        const header = $('preview-header');
        const box    = $('preview-box');
        const bodyEl = $('preview-body');
        const titleEl   = $('preview-title');
        const subEl     = $('preview-subtitle');
        const welcomeEl = $('preview-welcome');
        const avatarText = $('preview-avatar-text');
        const btnSvg = btn ? btn.querySelector('svg') : null;

        if (btn) { btn.style.background = primary; }
        if (btnSvg) { btnSvg.setAttribute('stroke', text); }
        if (header) header.style.background = primary;
        if (box)    box.style.borderRadius = radius + 'px';
        if (titleEl)    { titleEl.textContent = title;    titleEl.style.color = text; }
        if (subEl)      { subEl.textContent = subtitle;   subEl.style.color = text; }
        if (welcomeEl)  welcomeEl.textContent = welcome;
        if (avatarText) avatarText.style.color = text;

        if (bodyEl) {
            bodyEl.style.background = dark ? '#1e293b' : '#fff';
        }
        if (welcomeEl) {
            welcomeEl.style.background = dark ? '#334155' : '#f1f5f9';
            welcomeEl.style.color      = dark ? '#f1f5f9' : '#374151';
        }

        if (box && btn) {
            if (pos === 'bottom-left') {
                box.style.right = 'auto'; box.style.left = '16px';
                btn.style.right = 'auto'; btn.style.left = '16px';
            } else {
                box.style.left  = 'auto'; box.style.right = '16px';
                btn.style.left  = 'auto'; btn.style.right = '16px';
            }
        }
    }

    // Watch form fields
    ['widget_title','widget_subtitle','welcome_message','agent_name','border_radius'].forEach(id => {
        const el = $(id); if (el) el.addEventListener('input', updatePreview);
    });
    ['position','dark_mode'].forEach(id => {
        const el = $(id); if (el) el.addEventListener('change', updatePreview);
    });

    // Image upload previews
    function setupImagePreview(inputId, previewId, placeholderId) {
        const input = $(inputId), preview = $(previewId), placeholder = $(placeholderId);
        if (!input || !preview) return;
        input.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = e => {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    preview.classList.remove('hidden');
                    if (placeholder) placeholder.style.display = 'none';
                    if (inputId === 'company_image_input') {
                        const av = $('preview-avatar');
                        if (av) av.innerHTML = '<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover;border-radius:50%">';
                    }
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }

    setupImagePreview('system_logo_input',   'system_logo_preview',   'system_logo_placeholder');
    setupImagePreview('company_image_input', 'company_image_preview', 'company_image_placeholder');
    setupImagePreview('favicon_input',       'favicon_preview',       'favicon_placeholder');

    // Profile avatar live preview
    const profileInput = $('profile_avatar_input');
    const profilePreview = $('profile_avatar_preview');
    if (profileInput && profilePreview) {
        profileInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = e => { profilePreview.src = e.target.result; };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }

    updatePreview();

    // WhatsApp contacts — dynamic add/remove
    const waList = document.getElementById('wa-contacts-list');
    const waAddBtn = document.getElementById('wa-add-btn');

    function waRowIndex() {
        return waList ? waList.querySelectorAll('.wa-contact-row').length : 0;
    }

    function addWaRow() {
        const idx = waRowIndex();
        const row = document.createElement('div');
        row.className = 'wa-contact-row flex items-center gap-2';
        row.innerHTML = `
            <input type="text" name="whatsapp_contacts[${idx}][label]"
                placeholder="Label (مثلاً: Sales, Support)"
                class="w-36 flex-shrink-0 px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
            <span class="text-slate-400 text-sm flex-shrink-0">wa.me/</span>
            <input type="text" name="whatsapp_contacts[${idx}][number]"
                placeholder="923207560200"
                class="flex-1 px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
            <button type="button" onclick="this.closest('.wa-contact-row').remove()"
                class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-50 text-red-400 hover:bg-red-100 hover:text-red-600 transition-colors flex-shrink-0 cursor-pointer">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>`;
        waList.appendChild(row);
        row.querySelector('input').focus();
    }

    if (waAddBtn) waAddBtn.addEventListener('click', addWaRow);

    // Re-index name attributes before form submit so server gets 0,1,2...
    const widgetForm = document.getElementById('widget-form');
    if (widgetForm) {
        widgetForm.addEventListener('submit', function () {
            waList.querySelectorAll('.wa-contact-row').forEach(function (row, i) {
                const inputs = row.querySelectorAll('input[type="text"]');
                if (inputs[0]) inputs[0].name = `whatsapp_contacts[${i}][label]`;
                if (inputs[1]) inputs[1].name = `whatsapp_contacts[${i}][number]`;
            });
        });
    }
})();
</script>
@endpush
