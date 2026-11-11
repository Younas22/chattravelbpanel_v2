<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    @php
        $systemName = \App\Models\WidgetSetting::get('system_name', 'TBP Chat');
        $systemLogo = \App\Models\WidgetSetting::get('system_logo', '');
        $features = [
            ['Live chat',    'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z'],
            ['Tickets',      'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z'],
            ['Phone alerts', 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'],
            ['Analytics',    'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
        ];
        $chatIcon = 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z';
    @endphp
    <title>Sign in — {{ $systemName }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    @include('partials.theme-head')
    @include('partials.pwa-head')
</head>
<body class="min-h-dvh bg-white antialiased text-slate-900">
<div class="min-h-dvh lg:h-dvh grid lg:grid-cols-2">

    {{-- ═════════ Left: what this is (desktop / tablet landscape only) ═════════ --}}
    <section class="relative hidden lg:flex flex-col overflow-hidden border-r border-slate-100 px-10 xl:px-16 py-8">
        <div aria-hidden="true" class="pointer-events-none absolute inset-0 [background-image:radial-gradient(color-mix(in_oklab,var(--color-brand-200)_60%,transparent)_1px,transparent_1px)] [background-size:22px_22px] [mask-image:linear-gradient(to_bottom,black,transparent_85%)]"></div>
        <div aria-hidden="true" class="pointer-events-none absolute -top-24 -left-24 w-96 h-96 rounded-full bg-brand-100/60 blur-3xl"></div>

        {{-- Brand --}}
        <div class="relative flex items-center gap-3">
            @if($systemLogo)
                <img src="{{ url($systemLogo) }}" alt="{{ $systemName }}" class="w-10 h-10 rounded-xl object-cover shadow-sm">
            @else
                <div class="w-10 h-10 rounded-xl bg-brand-600 grid place-items-center shadow-sm">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $chatIcon }}"/></svg>
                </div>
            @endif
            <span class="font-semibold text-slate-900">{{ $systemName }}</span>
        </div>

        <div class="relative flex-1 min-h-0 flex flex-col justify-center max-w-lg">
            <span class="inline-flex w-fit items-center gap-2 rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-700 ring-1 ring-brand-100">
                <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                Customer support dashboard
            </span>
            <h2 class="mt-4 text-3xl xl:text-4xl font-bold tracking-tight leading-tight">
                Every customer conversation,
                <span class="text-brand-600">in one place.</span>
            </h2>
            <p class="mt-3 text-slate-500 leading-relaxed">
                Chats, tickets and visitor insights together — with instant alerts on your phone.
            </p>

            {{-- Mini chat preview (hidden on short screens so the page never scrolls) --}}
            <div aria-hidden="true" class="mt-7 w-full max-w-md rounded-2xl bg-white/90 backdrop-blur shadow-xl shadow-slate-200/70 ring-1 ring-slate-100 overflow-hidden [@media(max-height:700px)]:hidden">
                <div class="flex items-center gap-3 px-4 py-2.5 border-b border-slate-100">
                    <div class="relative">
                        <div class="w-8 h-8 rounded-full bg-brand-100 text-brand-700 grid place-items-center text-sm font-semibold">S</div>
                        <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-green-500 ring-2 ring-white"></span>
                    </div>
                    <div class="min-w-0 leading-tight">
                        <p class="text-sm font-semibold text-slate-900">Sara · Website visitor</p>
                        <p class="text-xs text-brand-600 font-medium">typing…</p>
                    </div>
                    <span class="ml-auto text-[11px] text-slate-400">now</span>
                </div>
                <div class="px-4 py-3 space-y-2 bg-slate-50/60">
                    <div class="flex"><p class="max-w-[80%] rounded-2xl rounded-bl-md bg-white px-3.5 py-2 text-sm text-slate-700 shadow-sm ring-1 ring-slate-100">Do you have Umrah packages for December? 🕋</p></div>
                    <div class="flex justify-end"><p class="max-w-[80%] rounded-2xl rounded-br-md bg-brand-600 px-3.5 py-2 text-sm text-white shadow-sm">Yes! Sending the details now 👍</p></div>
                </div>
            </div>

            {{-- Feature chips --}}
            <ul class="mt-6 flex flex-wrap gap-2">
                @foreach($features as [$label, $icon])
                <li class="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1.5 text-xs font-medium text-slate-700 ring-1 ring-slate-200 shadow-sm">
                    <svg class="w-3.5 h-3.5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/></svg>
                    {{ $label }}
                </li>
                @endforeach
            </ul>
        </div>

        <p class="relative text-xs text-slate-400">&copy; {{ date('Y') }} {{ $systemName }} &middot; Support System</p>
    </section>

    {{-- ═════════ Right: sign in ═════════ --}}
    <section class="flex flex-col justify-center px-6 sm:px-10 py-5 sm:py-8 lg:overflow-y-auto">
        <div class="w-full max-w-sm mx-auto">

            {{-- Brand (phones / small tablets) --}}
            <div class="lg:hidden flex items-center gap-3 mb-5 sm:mb-7">
                @if($systemLogo)
                    <img src="{{ url($systemLogo) }}" alt="{{ $systemName }}" class="w-10 h-10 rounded-xl object-cover shadow-sm">
                @else
                    <div class="w-10 h-10 rounded-xl bg-brand-600 grid place-items-center shadow-sm">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $chatIcon }}"/></svg>
                    </div>
                @endif
                <span class="font-semibold text-slate-900">{{ $systemName }}</span>
            </div>

            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight">Welcome back 👋</h1>
            <p class="mt-1.5 text-sm text-slate-500">Sign in to manage chats, tickets and visitors.</p>

            @if($errors->any())
                <div class="mt-4 sm:mt-5 flex items-start gap-2 p-3 bg-red-50 border border-red-100 rounded-xl text-red-600 text-sm" role="alert">
                    <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.post') }}" class="mt-5 sm:mt-6 space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">Email address</label>
                    <div class="relative">
                        <svg class="w-[18px] h-[18px] text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                            class="w-full pl-11 pr-3.5 py-3 rounded-xl border border-slate-200 bg-white text-[15px] sm:text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 transition"
                            placeholder="you@company.com">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">Password</label>
                    <div class="relative">
                        <svg class="w-[18px] h-[18px] text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <input id="password" type="password" name="password" required autocomplete="current-password"
                            class="w-full pl-11 pr-12 py-3 rounded-xl border border-slate-200 bg-white text-[15px] sm:text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 transition"
                            placeholder="Enter your password">
                        <button type="button" id="pw-toggle" aria-label="Show password"
                            class="absolute right-2 top-1/2 -translate-y-1/2 grid place-items-center w-9 h-9 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                            <svg id="pw-eye" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg id="pw-eye-off" class="hidden w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                </div>

                <label class="flex items-center gap-2.5 text-sm text-slate-600 cursor-pointer select-none w-fit">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    Keep me signed in
                </label>

                <button type="submit" id="login-submit"
                    class="group w-full inline-flex items-center justify-center gap-2 py-3 px-4 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-xl transition shadow-lg shadow-brand-600/20 text-[15px] sm:text-sm cursor-pointer disabled:opacity-70">
                    <span>Sign in to dashboard</span>
                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </button>
            </form>

            <p class="mt-4 flex items-center justify-center gap-1.5 text-xs text-slate-400">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Secure sign-in for authorised staff only
            </p>

            {{-- Get the app (compact) --}}
            <div id="app-install" class="hidden mt-5 sm:mt-6 rounded-2xl border border-slate-200 bg-slate-50/70 p-3">
                <div class="flex items-center gap-3">
                    <img src="{{ \App\Services\PwaIcons::url('icon-192.png') }}" alt="" class="w-10 h-10 rounded-xl shadow-sm shrink-0 bg-white object-cover">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-slate-900 truncate">Get the app</p>
                        <p class="text-xs text-slate-500 truncate">Instant alerts for new messages</p>
                    </div>
                    {{-- Android / Chrome / Edge / desktop --}}
                    <button type="button" id="app-install-btn"
                        class="hidden shrink-0 items-center gap-1.5 px-3 py-2 rounded-lg bg-white text-slate-800 text-xs font-semibold ring-1 ring-slate-200 hover:ring-brand-300 hover:text-brand-700 transition cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Install
                    </button>
                </div>

                {{-- iPhone / iPad (Safari has no install button) --}}
                <p id="app-install-ios" class="hidden mt-2 text-xs text-slate-600">
                    Tap <svg class="inline w-4 h-4 -mt-0.5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v12m0-12l-4 4m4-4l4 4M5 13v6a2 2 0 002 2h10a2 2 0 002-2v-6"/></svg>
                    <b>Share</b> → <b>Add to Home Screen</b>, then open the app and turn on notifications.
                </p>

                {{-- Other browsers --}}
                <p id="app-install-other" class="hidden mt-2 text-xs text-slate-500">Open this page in <b>Chrome</b> (Android) or <b>Safari</b> (iPhone) to install the app.</p>
            </div>
        </div>
    </section>
</div>

<script>
// Show / hide password
(function () {
    const input = document.getElementById('password');
    const btn = document.getElementById('pw-toggle');
    btn.addEventListener('click', () => {
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        document.getElementById('pw-eye').classList.toggle('hidden', show);
        document.getElementById('pw-eye-off').classList.toggle('hidden', !show);
        input.focus();
    });
    // prevent double submits
    document.querySelector('form').addEventListener('submit', () => {
        const b = document.getElementById('login-submit');
        b.disabled = true;
        b.querySelector('span').textContent = 'Signing in…';
    });
})();

// "Get the app" card
(function () {
    if (window.TBP_PWA && window.TBP_PWA.standalone) return;          // already running as the app
    const box = document.getElementById('app-install');
    const btn = document.getElementById('app-install-btn');
    const ua = navigator.userAgent;
    const isIOS = /iphone|ipad|ipod/i.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    const show = (id) => { box.classList.remove('hidden'); document.getElementById(id).classList.remove('hidden'); if (id === 'app-install-btn') btn.classList.add('inline-flex'); };
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
