<!DOCTYPE html>
<html lang="en" x-data="{ sidebarOpen: window.innerWidth >= 1024 }" :class="{ 'dark': false }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — TravelBookingPanel Chat</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Inter', sans-serif; }
        .sidebar-link { @apply flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200; }
        .sidebar-link.active { @apply bg-blue-600 text-white shadow-sm; }
        .sidebar-link:not(.active) { @apply text-slate-600 hover:bg-slate-100 hover:text-slate-900; }
        .card { @apply bg-white rounded-2xl shadow-sm border border-slate-100 p-6; }
        .btn-primary { @apply inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition-all duration-200 shadow-sm; }
        .btn-secondary { @apply inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium rounded-xl transition-all duration-200; }
        .btn-danger { @apply inline-flex items-center gap-2 px-4 py-2 bg-red-50 hover:bg-red-100 text-red-600 text-sm font-medium rounded-xl transition-all duration-200; }
        .badge { @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium; }
        .input { @apply block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors; }
        [x-cloak] { display: none !important; }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-50 antialiased">

<div class="flex h-screen overflow-hidden">

    {{-- Sidebar --}}
    <aside
        class="fixed inset-y-0 left-0 z-50 flex flex-col bg-white border-r border-slate-100 shadow-sm transition-all duration-300"
        :class="sidebarOpen ? 'w-64' : 'w-0 lg:w-16 overflow-hidden'"
        x-cloak
    >
        {{-- Logo --}}
        <div class="flex items-center gap-3 px-4 h-16 border-b border-slate-100 shrink-0">
            <div class="w-8 h-8 bg-blue-600 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
            </div>
            <div x-show="sidebarOpen" x-transition:enter="transition-opacity duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <p class="text-sm font-bold text-slate-900 whitespace-nowrap">TBP Chat</p>
                <p class="text-xs text-slate-500">Support Dashboard</p>
            </div>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 overflow-y-auto p-3 space-y-1">
            <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Dashboard</span>
            </a>

            <a href="{{ route('admin.conversations.index') }}" class="sidebar-link {{ request()->routeIs('admin.conversations*') ? 'active' : '' }}">
                <div class="relative shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                    @php $unread = \App\Models\Conversation::sum('unread_admin'); @endphp
                    @if($unread > 0)
                        <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-[10px] rounded-full flex items-center justify-center font-bold">{{ $unread > 9 ? '9+' : $unread }}</span>
                    @endif
                </div>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Live Chats</span>
            </a>

            <a href="{{ route('admin.tickets.index') }}" class="sidebar-link {{ request()->routeIs('admin.tickets*') ? 'active' : '' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Tickets</span>
            </a>

            <a href="{{ route('admin.visitors.index') }}" class="sidebar-link {{ request()->routeIs('admin.visitors*') ? 'active' : '' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Visitors</span>
            </a>

            <a href="{{ route('admin.analytics.index') }}" class="sidebar-link {{ request()->routeIs('admin.analytics*') ? 'active' : '' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Analytics</span>
            </a>

            <div class="pt-2 pb-1" x-show="sidebarOpen">
                <p class="px-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Configure</p>
            </div>

            <a href="{{ route('admin.canned-replies.index') }}" class="sidebar-link {{ request()->routeIs('admin.canned-replies*') ? 'active' : '' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h7"/></svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Canned Replies</span>
            </a>

            <a href="{{ route('admin.faqs.index') }}" class="sidebar-link {{ request()->routeIs('admin.faqs*') ? 'active' : '' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Quick FAQs</span>
            </a>

            <a href="{{ route('admin.settings.widget') }}" class="sidebar-link {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Settings</span>
            </a>
        </nav>

        {{-- User --}}
        <div class="p-3 border-t border-slate-100 shrink-0">
            <div class="flex items-center gap-3">
                <img src="{{ auth()->user()->avatar_url }}" alt="" class="w-8 h-8 rounded-full shrink-0">
                <div x-show="sidebarOpen" class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-slate-900 truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-slate-500 truncate">Administrator</p>
                </div>
                <form method="POST" action="{{ route('admin.logout') }}" x-show="sidebarOpen">
                    @csrf
                    <button type="submit" class="text-slate-400 hover:text-red-500 transition-colors" title="Logout">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Main Content --}}
    <div class="flex-1 flex flex-col min-w-0 transition-all duration-300" :class="sidebarOpen ? 'lg:ml-64' : 'lg:ml-16'">

        {{-- Top Header --}}
        <header class="flex items-center gap-4 h-16 px-4 lg:px-6 bg-white border-b border-slate-100 sticky top-0 z-40">
            <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-xl hover:bg-slate-100 text-slate-500 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>

            <div class="flex-1">
                <h1 class="text-lg font-semibold text-slate-900">@yield('title', 'Dashboard')</h1>
            </div>

            {{-- Online indicator --}}
            <div class="flex items-center gap-2 text-xs text-slate-500">
                <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                <span id="live-visitors-count">—</span> online
            </div>

            {{-- Bell notification button --}}
            <button id="admin-bell-btn" onclick="window.location='{{ route('admin.conversations.index') }}'"
                class="p-2 rounded-xl hover:bg-slate-100 text-slate-500 transition-colors relative">
                <svg id="admin-bell-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span id="admin-bell-badge" class="hidden absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-[9px] font-bold rounded-full flex items-center justify-content-center leading-none" style="display:none!important;align-items:center;justify-content:center;"></span>
            </button>

            {{-- Toast container --}}
            <div id="admin-toast-container" style="position:fixed;top:16px;right:16px;z-index:9999;display:flex;flex-direction:column;gap:8px;pointer-events:none;"></div>
        </header>

        {{-- Page Content --}}
        <main class="flex-1 overflow-y-auto p-4 lg:p-6">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

</div>

@stack('scripts')
<script>
(function () {
    let prevVisitors = null;
    let prevUnread = null;

    function playBell(file) {
        try {
            const a = new Audio('/voice/' + file);
            a.volume = 0.7;
            a.play().catch(() => {});
        } catch (e) {}
    }

    function showToast(msg, icon) {
        const c = document.getElementById('admin-toast-container');
        if (!c) return;
        const t = document.createElement('div');
        t.style.cssText = 'background:#1e293b;color:#f1f5f9;padding:10px 14px;border-radius:12px;font-size:13px;font-family:Inter,sans-serif;display:flex;align-items:center;gap:8px;box-shadow:0 8px 24px rgba(0,0,0,0.25);pointer-events:auto;min-width:220px;animation:tbpSlideIn 0.3s ease;';
        t.innerHTML = '<span style="font-size:18px">' + icon + '</span><span>' + msg + '</span>';
        c.appendChild(t);
        setTimeout(() => { t.style.opacity = '0'; t.style.transition = 'opacity 0.4s'; setTimeout(() => t.remove(), 400); }, 4000);
    }

    function updateBellBadge(count) {
        const badge = document.getElementById('admin-bell-badge');
        const icon = document.getElementById('admin-bell-icon');
        if (!badge) return;
        if (count > 0) {
            badge.textContent = count > 9 ? '9+' : count;
            badge.style.display = 'flex';
            icon && icon.setAttribute('stroke', '#ef4444');
        } else {
            badge.style.display = 'none';
            icon && icon.setAttribute('stroke', 'currentColor');
        }
    }

    function updateStats() {
        fetch('{{ route('admin.stats') }}')
            .then(r => r.json())
            .then(data => {
                const el = document.getElementById('live-visitors-count');
                if (el) el.textContent = data.active_visitors;

                // New visitor landed
                if (prevVisitors !== null && data.active_visitors > prevVisitors) {
                    playBell('newvisitor.wav');
                    showToast('New visitor on the site', '👋');
                }
                prevVisitors = data.active_visitors;

                // New unread message
                const unread = data.unread_messages || 0;
                if (prevUnread !== null && unread > prevUnread) {
                    // Only play chat sound if we're NOT already on a conversation show page
                    // (show page has its own sound trigger)
                    if (!document.getElementById('messages-container')) {
                        playBell('chat.wav');
                        showToast('New message received', '💬');
                    }
                }
                prevUnread = unread;
                updateBellBadge(unread);
            })
            .catch(() => {});
    }

    updateStats();
    setInterval(updateStats, 10000);
})();
</script>
</body>
</html>
