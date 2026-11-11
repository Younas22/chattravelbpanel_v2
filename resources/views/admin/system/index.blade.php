@extends('admin.layouts.app')
@section('title', 'System Update')

@section('content')
<div class="max-w-3xl mx-auto space-y-4" x-data="systemUpdate(@js($status))">

    {{-- Intro --}}
    <div class="card">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-brand-50 text-brand-600 grid place-items-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </div>
            <div class="min-w-0 flex-1">
                <h2 class="font-semibold text-slate-900">Update the server</h2>
                <p class="text-sm text-slate-500 mt-1">After new code is uploaded, press <b>Run all updates</b>. It installs new packages, updates the database, sets up push notifications and clears old caches — no terminal needed.</p>
                <button type="button" @click="runAll()" :disabled="busy"
                        class="btn-primary mt-4 !px-5 !py-2.5 disabled:opacity-60 disabled:cursor-wait">
                    <svg x-show="!busy" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <svg x-show="busy" x-cloak class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-25"/><path fill="currentColor" class="opacity-75" d="M4 12a8 8 0 018-8v3a5 5 0 00-5 5H4z"/></svg>
                    <span x-text="busy ? 'Updating… please keep this page open' : 'Run all updates'"></span>
                </button>
                <p x-show="finished" x-cloak class="mt-3 text-sm font-medium" :class="allOk ? 'text-green-700' : 'text-red-600'"
                   x-text="allOk ? '✓ Everything is up to date.' : 'Some steps failed — see the details below.'"></p>
            </div>
        </div>
    </div>

    {{-- Health checklist --}}
    <div class="card">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold text-slate-900 text-sm">Server health</h3>
            <button type="button" @click="refresh()" class="text-xs font-medium text-brand-600 hover:underline cursor-pointer">Refresh</button>
        </div>
        <ul class="divide-y divide-slate-100 text-sm">
            <template x-for="c in checks()" :key="c.label">
                <li class="flex items-start gap-3 py-2.5">
                    <span class="mt-0.5 grid place-items-center w-5 h-5 rounded-full shrink-0 text-white text-[11px] font-bold"
                          :class="c.ok ? 'bg-green-500' : (c.warn ? 'bg-amber-500' : 'bg-red-500')" x-text="c.ok ? '✓' : (c.warn ? '!' : '✕')"></span>
                    <div class="min-w-0">
                        <p class="font-medium text-slate-800" x-text="c.label"></p>
                        <p class="text-xs text-slate-500" x-text="c.ok ? c.okText : c.badText"></p>
                    </div>
                </li>
            </template>
        </ul>
    </div>

    {{-- Steps --}}
    <div class="card !p-0 overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-semibold text-slate-900 text-sm">Steps</h3>
            <span class="text-xs text-slate-400">Each step can also be run on its own</span>
        </div>
        <template x-for="(s, i) in steps" :key="s.key">
            <div class="border-b border-slate-100 last:border-0">
                <div class="flex items-center gap-3 px-4 sm:px-6 py-3.5">
                    <span class="grid place-items-center w-8 h-8 rounded-full shrink-0 text-sm font-semibold"
                          :class="{
                              'bg-slate-100 text-slate-500': s.state === 'idle',
                              'bg-brand-100 text-brand-700': s.state === 'running',
                              'bg-green-100 text-green-700': s.state === 'ok' || s.state === 'skipped',
                              'bg-red-100 text-red-600': s.state === 'fail'
                          }">
                        <span x-show="s.state === 'idle'" x-text="i + 1"></span>
                        <svg x-show="s.state === 'running'" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-25"/><path fill="currentColor" class="opacity-75" d="M4 12a8 8 0 018-8v3a5 5 0 00-5 5H4z"/></svg>
                        <span x-show="s.state === 'ok' || s.state === 'skipped'">✓</span>
                        <span x-show="s.state === 'fail'">✕</span>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-slate-800" x-text="s.title"></p>
                        <p class="text-xs text-slate-500" x-text="s.state === 'skipped' ? 'Not needed — already done' : s.desc"></p>
                    </div>
                    <button type="button" x-show="s.output" @click="s.open = !s.open" class="text-xs font-medium text-slate-500 hover:text-slate-800 cursor-pointer shrink-0"
                            x-text="s.open ? 'Hide details' : 'Details'"></button>
                    <button type="button" @click="runStep(s)" :disabled="busy" class="btn-secondary !px-3 !py-1.5 !text-xs shrink-0 disabled:opacity-50">Run</button>
                </div>
                <pre x-show="s.open && s.output" x-cloak class="mx-4 sm:mx-6 mb-4 max-h-72 overflow-auto thin-scroll rounded-xl bg-slate-900 text-slate-100 text-[11px] leading-relaxed p-3 whitespace-pre-wrap break-words" x-text="s.output"></pre>

                {{-- Composer missing: offer to download it --}}
                <div x-show="s.key === 'composer' && !status.composer && status.shell" x-cloak class="mx-4 sm:mx-6 mb-4 flex flex-wrap items-center gap-3 rounded-xl bg-amber-50 border border-amber-200 px-3 py-2.5">
                    <p class="text-xs text-amber-800 flex-1 min-w-[12rem]">Composer isn't installed on this server. Download it once (from getcomposer.org, checksum verified), then run the updates.</p>
                    <button type="button" @click="downloadComposer()" :disabled="busy" class="btn-secondary !px-3 !py-1.5 !text-xs">Download Composer</button>
                </div>
            </div>
        </template>
    </div>

    <p class="text-xs text-slate-400 text-center">PHP <span x-text="status.php"></span> · <span x-text="status.env"></span> environment</p>
</div>
@endsection

@push('scripts')
<script>
function systemUpdate(initial) {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const runUrl = (step) => @json(url('admin/system/run')) + '/' + step;
    return {
        status: initial,
        busy: false, finished: false, allOk: false,
        steps: [
            { key: 'composer', title: 'Install packages',        desc: 'Runs composer install (adds new libraries such as web-push)', state: 'idle', output: '', open: false },
            { key: 'migrate',  title: 'Update database',          desc: 'Runs new database migrations',                                 state: 'idle', output: '', open: false },
            { key: 'vapid',    title: 'Set up push notifications', desc: 'Creates this server\'s push keys (only if missing)',          state: 'idle', output: '', open: false },
            { key: 'clear',    title: 'Clear caches',             desc: 'Clears config, route and view caches so changes take effect', state: 'idle', output: '', open: false },
        ],

        checks() {
            const s = this.status;
            return [
                { label: 'Push library (web-push)', ok: s.web_push, okText: 'Installed', badText: 'Not installed — run "Install packages"' },
                { label: 'Database', ok: s.pending === 0, warn: s.pending === null,
                  okText: 'Up to date', badText: s.pending === null ? 'Could not check' : s.pending + ' update(s) waiting — run "Update database"' },
                { label: 'Push notification keys', ok: s.vapid, okText: 'Configured', badText: 'Missing — run "Set up push notifications"' },
                { label: 'Encryption (OpenSSL)', ok: s.openssl_ec, okText: 'Working', badText: 'PHP can\'t create encryption keys — push will not work. Ask your host to fix the OpenSSL config.' },
                { label: 'HTTPS', ok: s.https, warn: !s.https, okText: 'Secure — app install & push allowed', badText: 'Site is not on https:// — phones won\'t install the app or receive push' },
                { label: 'Composer', ok: !!s.composer, warn: !s.composer && s.shell, okText: 'Found (' + (s.composer || '') + ')',
                  badText: s.shell ? 'Not found — use "Download Composer" below' : 'This server blocks running commands (proc_open). Packages must be installed by your host.' },
            ];
        },

        async post(step) {
            try {
                const r = await fetch(runUrl(step), { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } });
                const text = await r.text();
                try { return JSON.parse(text); }
                catch (e) { return { ok: false, output: 'Server error (HTTP ' + r.status + '). ' + text.replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').slice(0, 400) }; }
            } catch (e) {
                return { ok: false, output: 'Network error: ' + e.message };
            }
        },

        async runStep(s) {
            s.state = 'running'; s.output = ''; s.open = false;
            const res = await this.post(s.key);
            s.output = res.output || '';
            if (!res.ok && s.key === 'composer' && /HTTP 5\d\d|Network error/.test(s.output)) {
                s.output += '\n\nThe connection timed out, but installing packages may still be finishing on the server. '
                          + 'Wait a minute, press "Refresh" under Server health, and if "Push library" shows ✓ run the remaining steps.';
            }
            s.state = res.skipped ? 'skipped' : (res.ok ? 'ok' : 'fail');
            if (s.state === 'fail') s.open = true;
            return s.state !== 'fail';
        },

        async runAll() {
            if (this.busy) return;
            this.busy = true; this.finished = false;
            this.steps.forEach(s => { s.state = 'idle'; s.output = ''; s.open = false; });
            let ok = true;
            for (const s of this.steps) {
                if (!(await this.runStep(s))) { ok = false; break; }   // stop at the first failure
            }
            this.allOk = ok; this.finished = true; this.busy = false;
            this.refresh();
        },

        async downloadComposer() {
            this.busy = true;
            const s = this.steps[0];
            s.state = 'running';
            const res = await this.post('download-composer');
            s.output = res.output || ''; s.open = true;
            s.state = res.ok ? 'idle' : 'fail';
            this.busy = false;
            this.refresh();
        },

        async refresh() {
            try { this.status = await (await fetch(@json(route('admin.system.status')), { headers: { 'Accept': 'application/json' } })).json(); } catch (e) {}
        },
    };
}
</script>
@endpush
