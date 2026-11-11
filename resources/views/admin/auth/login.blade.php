<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    @php
        $systemName = \App\Models\WidgetSetting::get('system_name', 'TBP Chat');
        $systemLogo = \App\Models\WidgetSetting::get('system_logo', '');
    @endphp
    <title>Admin Login — {{ $systemName }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    @include('partials.theme-head')
    @include('partials.pwa-head')
</head>
<body class="min-h-dvh bg-gradient-to-br from-slate-900 via-brand-950 to-slate-900 flex items-center justify-center p-4">
    <div class="w-full max-w-sm">

        {{-- Logo --}}
        <div class="text-center mb-8">
            @if($systemLogo)
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl shadow-xl mb-4 overflow-hidden bg-white">
                    <img src="{{ url($systemLogo) }}" alt="{{ $systemName }}" class="w-full h-full object-cover">
                </div>
            @else
                <div class="inline-flex items-center justify-center w-16 h-16 bg-brand-600 rounded-2xl shadow-xl mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                </div>
            @endif
            <h1 class="text-2xl font-bold text-white">{{ $systemName }}</h1>
            <p class="text-slate-400 text-sm mt-1">Sign in to your dashboard</p>
        </div>

        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            @if($errors->any())
                <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-red-600 text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.post') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent text-sm transition-all"
                        placeholder="admin@example.com">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Password</label>
                    <input type="password" name="password" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent text-sm transition-all"
                        placeholder="••••••••">
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-sm text-slate-600 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                        Remember me
                    </label>
                </div>

                <button type="submit"
                    class="w-full py-2.5 px-4 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-xl transition-all duration-200 text-sm shadow-sm shadow-brand-200 mt-2 cursor-pointer">
                    Sign in to Dashboard
                </button>
            </form>
        </div>

        {{-- Get the app --}}
        <div id="app-install" class="hidden mt-5 rounded-2xl bg-white/10 backdrop-blur ring-1 ring-white/15 p-4 text-white">
            <div class="flex items-center gap-3">
                <img src="{{ \App\Services\PwaIcons::url('icon-192.png') }}" alt="" class="w-11 h-11 rounded-xl shadow-lg shrink-0 bg-white object-cover">
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold">Get the {{ $systemName }} app</p>
                    <p class="text-xs text-slate-300">Install on your phone · instant alerts for new messages</p>
                </div>
            </div>

            {{-- Android / Chrome / Edge / desktop --}}
            <button type="button" id="app-install-btn"
                class="hidden mt-3 w-full items-center justify-center gap-2 py-2.5 rounded-xl bg-white text-slate-900 text-sm font-semibold hover:bg-slate-100 transition-colors cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Install app
            </button>

            {{-- iPhone / iPad (Safari has no install button) --}}
            <ol id="app-install-ios" class="hidden mt-3 space-y-2 text-xs text-slate-200">
                <li class="flex items-center gap-2"><span class="grid place-items-center w-5 h-5 rounded-full bg-white/15 text-[10px] font-bold shrink-0">1</span>
                    <span>Tap <svg class="inline w-4 h-4 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v12m0-12l-4 4m4-4l4 4M5 13v6a2 2 0 002 2h10a2 2 0 002-2v-6"/></svg> <b>Share</b> in Safari's toolbar</span></li>
                <li class="flex items-center gap-2"><span class="grid place-items-center w-5 h-5 rounded-full bg-white/15 text-[10px] font-bold shrink-0">2</span>
                    <span>Choose <b>Add to Home Screen</b></span></li>
                <li class="flex items-center gap-2"><span class="grid place-items-center w-5 h-5 rounded-full bg-white/15 text-[10px] font-bold shrink-0">3</span>
                    <span>Open the app from your home screen and turn on notifications</span></li>
            </ol>

            {{-- Other browsers --}}
            <p id="app-install-other" class="hidden mt-3 text-xs text-slate-300">Open this page in <b>Chrome</b> (Android) or <b>Safari</b> (iPhone) to install the app.</p>
        </div>

        <p class="text-center text-slate-500 text-xs mt-6">{{ $systemName }} &mdash; Support System</p>
    </div>

<script>
(function () {
    if (window.TBP_PWA && window.TBP_PWA.standalone) return;          // already running as the app
    const box = document.getElementById('app-install');
    const btn = document.getElementById('app-install-btn');
    const ua = navigator.userAgent;
    const isIOS = /iphone|ipad|ipod/i.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    const show = (id) => { box.classList.remove('hidden'); document.getElementById(id).classList.remove('hidden'); if (id === 'app-install-btn') btn.classList.add('flex'); };
    let deferred = null;

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        if (isIOS) return;                                             // iOS installs via Share → Add to Home Screen only
        deferred = e;
        document.getElementById('app-install-other').classList.add('hidden');
        show('app-install-btn');
    });
    btn.addEventListener('click', async () => {
        if (!deferred) return;
        deferred.prompt();
        const choice = await deferred.userChoice;
        deferred = null;
        if (choice.outcome === 'accepted') box.classList.add('hidden');
    });
    window.addEventListener('appinstalled', () => box.classList.add('hidden'));

    if (isIOS) { show('app-install-ios'); return; }
    // Phones on browsers without an install prompt: point them to one that has it.
    setTimeout(() => { if (!deferred && /android|mobile/i.test(ua)) show('app-install-other'); }, 2500);
})();
</script>
</body>
</html>
