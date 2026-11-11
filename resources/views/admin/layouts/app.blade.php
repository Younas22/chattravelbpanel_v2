<!DOCTYPE html>
<html lang="en" x-data="{ sidebarOpen: window.innerWidth >= 1024 }" :class="{ 'dark': false }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $systemName    = \App\Models\WidgetSetting::get('system_name', 'TBP Chat');
        $systemLogo    = \App\Models\WidgetSetting::get('system_logo', '');
        $widgetAvatar  = \App\Models\WidgetSetting::get('company_image', '');
        $faviconPath   = \App\Models\WidgetSetting::get('favicon', '');
        $soundEnabled  = \App\Models\WidgetSetting::get('sound_enabled', 'true');
    @endphp
    <title>@yield('title', 'Dashboard') — {{ $systemName }}</title>
    @if($faviconPath)
        <link rel="icon" href="{{ url($faviconPath) }}">
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.theme-head')
    @include('partials.pwa-head')
    @stack('styles')
</head>
<body class="bg-slate-50 antialiased overscroll-none [-webkit-tap-highlight-color:transparent]">

<div class="flex h-dvh overflow-hidden">

    {{-- Mobile backdrop for the off-canvas sidebar --}}
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
         x-transition:enter="transition-opacity duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-[1px] lg:hidden"></div>

    {{-- Sidebar --}}
    <aside
        class="fixed inset-y-0 left-0 z-50 flex flex-col bg-white border-r border-slate-100 shadow-xl lg:shadow-sm transition-all duration-300"
        :class="sidebarOpen ? 'w-64' : 'w-0 lg:w-16 is-collapsed'"
        :style="(!sidebarOpen && window.innerWidth >= 1024) ? 'overflow:visible' : 'overflow:hidden'"
        x-cloak
    >
        {{-- Logo / System Branding --}}
        <div class="flex items-center gap-3 px-4 h-16 border-b border-slate-100 shrink-0">
            @if($systemLogo)
                <img src="{{ url($systemLogo) }}" class="w-8 h-8 rounded-xl object-cover shrink-0" alt="{{ $systemName }}">
            @else
                <div class="w-8 h-8 bg-brand-600 rounded-xl flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                </div>
            @endif
            <div x-show="sidebarOpen" x-transition:enter="transition-opacity duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <p class="text-sm font-bold text-slate-900 whitespace-nowrap">{{ $systemName }}</p>
                <p class="text-xs text-slate-500">Support Dashboard</p>
            </div>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 overflow-y-auto thin-scroll p-3 space-y-1" @click="if ($event.target.closest('a') && window.innerWidth < 1024) sidebarOpen = false">

            <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Dashboard</span>
                <span class="sidebar-tooltip" x-show="!sidebarOpen">Dashboard</span>
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
                <span class="sidebar-tooltip" x-show="!sidebarOpen">Live Chats</span>
            </a>

            <a href="{{ route('admin.tickets.index') }}" class="sidebar-link {{ request()->routeIs('admin.tickets*') ? 'active' : '' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Tickets</span>
                <span class="sidebar-tooltip" x-show="!sidebarOpen">Tickets</span>
            </a>

            <a href="{{ route('admin.visitors.index') }}" class="sidebar-link {{ request()->routeIs('admin.visitors*') ? 'active' : '' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Visitors</span>
                <span class="sidebar-tooltip" x-show="!sidebarOpen">Visitors</span>
            </a>

            <a href="{{ route('admin.leads.index') }}" class="sidebar-link {{ request()->routeIs('admin.leads*') ? 'active' : '' }}">
                <div class="relative shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 8v6m3-3h-6"/></svg>
                    @php
                        $pendingLeads = \App\Models\Visitor::where('lead_status', 'pending')
                            ->where(function ($q) { $q->whereNotNull('name')->orWhereNotNull('email')->orWhereNotNull('whatsapp'); })
                            ->count();
                    @endphp
                    @if($pendingLeads > 0)
                        <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-[10px] rounded-full flex items-center justify-center font-bold">{{ $pendingLeads > 9 ? '9+' : $pendingLeads }}</span>
                    @endif
                </div>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Leads</span>
                <span class="sidebar-tooltip" x-show="!sidebarOpen">Leads</span>
            </a>

            <a href="{{ route('admin.ticket-users.index') }}" class="sidebar-link {{ request()->routeIs('admin.ticket-users*') ? 'active' : '' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Ticket Users</span>
                <span class="sidebar-tooltip" x-show="!sidebarOpen">Ticket Users</span>
            </a>

            <a href="{{ route('admin.messages.index') }}" class="sidebar-link {{ request()->routeIs('admin.messages*') ? 'active' : '' }}">
                <div class="relative shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                    @php $dmUnread = \App\Models\DirectMessage::where('sender_type', 'ticket_user')->where('recipient_type', 'admin')->where('is_read', false)->count(); @endphp
                    @if($dmUnread > 0)
                        <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-[10px] rounded-full flex items-center justify-center font-bold">{{ $dmUnread > 9 ? '9+' : $dmUnread }}</span>
                    @endif
                </div>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Messages</span>
                <span class="sidebar-tooltip" x-show="!sidebarOpen">Messages</span>
            </a>

            <a href="{{ route('admin.analytics.index') }}" class="sidebar-link {{ request()->routeIs('admin.analytics*') ? 'active' : '' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Analytics</span>
                <span class="sidebar-tooltip" x-show="!sidebarOpen">Analytics</span>
            </a>

            <a href="{{ route('admin.countries.index') }}" class="sidebar-link {{ request()->routeIs('admin.countries*') ? 'active' : '' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Countries</span>
                <span class="sidebar-tooltip" x-show="!sidebarOpen">Countries</span>
            </a>

            <div class="pt-2 pb-1" x-show="sidebarOpen">
                <p class="px-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Configure</p>
            </div>
            <div class="pt-2 pb-1" x-show="!sidebarOpen">
                <div class="border-t border-slate-100 mx-1"></div>
            </div>

            <a href="{{ route('admin.canned-replies.index') }}" class="sidebar-link {{ request()->routeIs('admin.canned-replies*') ? 'active' : '' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h7"/></svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Canned Replies</span>
                <span class="sidebar-tooltip" x-show="!sidebarOpen">Canned Replies</span>
            </a>

            <a href="{{ route('admin.faqs.index') }}" class="sidebar-link {{ request()->routeIs('admin.faqs*') ? 'active' : '' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Quick FAQs</span>
                <span class="sidebar-tooltip" x-show="!sidebarOpen">Quick FAQs</span>
            </a>

            <a href="{{ route('admin.offers.index') }}" class="sidebar-link {{ request()->routeIs('admin.offers*') ? 'active' : '' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Global Offer</span>
                <span class="sidebar-tooltip" x-show="!sidebarOpen">Global Offer</span>
            </a>

            <a href="{{ route('admin.settings.widget') }}" class="sidebar-link {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Settings</span>
                <span class="sidebar-tooltip" x-show="!sidebarOpen">Settings</span>
            </a>
        </nav>

        {{-- Personal Branding / User Profile --}}
        <div class="p-3 border-t border-slate-100 shrink-0">
            @php
                $authUser = auth()->user();
                $userInitial = strtoupper(substr($authUser->name, 0, 1));
            @endphp
            {{-- Expanded view --}}
            <div x-show="sidebarOpen" class="flex items-center gap-3">
                <div class="relative shrink-0">
                    @if($widgetAvatar)
                        <img src="{{ url($widgetAvatar) }}" alt="{{ $authUser->name }}"
                             class="w-9 h-9 rounded-full ring-2 ring-brand-100 object-cover"
                             onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                        <div class="w-9 h-9 bg-brand-600 rounded-full ring-2 ring-brand-100 items-center justify-center" style="display:none">
                            <span class="text-white text-sm font-bold">{{ $userInitial }}</span>
                        </div>
                    @else
                        <div class="w-9 h-9 bg-brand-600 rounded-full ring-2 ring-brand-100 flex items-center justify-center">
                            <span class="text-white text-sm font-bold">{{ $userInitial }}</span>
                        </div>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-900 truncate">{{ $authUser->name }}</p>
                    <p class="text-xs text-slate-500 truncate">Administrator</p>
                </div>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit"
                        class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-500 hover:text-red-600 transition-colors text-xs font-medium cursor-pointer"
                        title="Logout">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
            {{-- Collapsed view --}}
            <div x-show="!sidebarOpen" class="sidebar-link">
                <div class="relative shrink-0 w-8 h-8">
                    @if($widgetAvatar)
                        <img src="{{ url($widgetAvatar) }}" alt="{{ $authUser->name }}"
                             class="block w-8 h-8 shrink-0 rounded-full ring-2 ring-brand-100 object-cover cursor-pointer"
                             onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                        <div class="w-8 h-8 bg-brand-600 rounded-full ring-2 ring-brand-100 items-center justify-center cursor-pointer" style="display:none">
                            <span class="text-white text-xs font-bold">{{ $userInitial }}</span>
                        </div>
                    @else
                        <div class="w-8 h-8 bg-brand-600 rounded-full ring-2 ring-brand-100 flex items-center justify-center cursor-pointer">
                            <span class="text-white text-xs font-bold">{{ $userInitial }}</span>
                        </div>
                    @endif
                </div>
                <span class="sidebar-tooltip">{{ $authUser->name }}</span>
            </div>
        </div>
    </aside>

    {{-- Main Content --}}
    <div class="flex-1 flex flex-col min-w-0 min-h-0 transition-all duration-300" :class="sidebarOpen ? 'lg:ml-64' : 'lg:ml-16'">

        {{-- Top Header --}}
        {{-- Chat detail pages hide this bar on mobile and show their own chat header instead --}}
        <header class="@hasSection('chat') hidden lg:flex @else flex @endif shrink-0 items-center gap-2 sm:gap-3 h-14 lg:h-16 px-3 lg:px-6 bg-white/90 backdrop-blur border-b border-slate-100 sticky top-0 z-30">
            <button @click="sidebarOpen = !sidebarOpen" class="icon-btn" aria-label="Toggle menu">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>

            <div class="flex-1 min-w-0">
                <h1 class="text-base lg:text-lg font-semibold text-slate-900 truncate">@yield('title', 'Dashboard')</h1>
            </div>

            {{-- Online indicator --}}
            <div class="flex items-center gap-1.5 rounded-full bg-green-50 px-2.5 py-1 text-xs font-medium text-green-700" title="Visitors online">
                <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                <span id="live-visitors-count">—</span><span class="hidden sm:inline">online</span>
            </div>

            {{-- Bell notification button --}}
            <button id="admin-bell-btn" onclick="window.location='{{ route('admin.conversations.index') }}'"
                class="icon-btn relative" aria-label="Live chats">
                <svg id="admin-bell-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span id="admin-bell-badge" class="hidden absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-[9px] font-bold rounded-full flex items-center justify-content-center leading-none" style="display:none!important;align-items:center;justify-content:center;"></span>
            </button>

            {{-- Theme picker --}}
            @include('partials.theme-picker')
        </header>

        {{-- Toast container --}}
        <div id="admin-toast-container" class="fixed top-4 right-4 left-4 sm:left-auto z-[9999] flex flex-col items-end gap-2 pointer-events-none"></div>

        {{-- Page Content --}}
        <main class="flex-1 min-h-0 @hasSection('chat') overflow-hidden lg:p-4 @else overflow-y-auto p-3 sm:p-4 lg:p-6 @endif">
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
// Collapsed-sidebar tooltips are position:fixed; place each one next to its link on hover.
document.addEventListener('mouseover', function (e) {
    const link = e.target.closest && e.target.closest('aside .sidebar-link');
    if (!link) return;
    const tip = link.querySelector('.sidebar-tooltip');
    if (!tip) return;
    const r = link.getBoundingClientRect();
    tip.style.top = (r.top + r.height / 2) + 'px';
    tip.style.left = (r.right + 10) + 'px';
});

(function () {
    let prevVisitors = null;
    let prevUnread = null;
    let lastRing = 0;
    const soundEnabled = {{ $soundEnabled === 'true' ? 'true' : 'false' }};

    function playBell(file) {
        if (!soundEnabled) return;
        try {
            const a = new Audio('{{ url('/') }}/voice/' + file);
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

                if (prevVisitors !== null && data.active_visitors > prevVisitors) {
                    playBell('newvisitor.mp3');
                    showToast('New visitor on the site', '👋');
                }
                prevVisitors = data.active_visitors;

                // live chats + customer messages + ticket replies
                const unread = (data.unread_messages || 0) + (data.dm_unread || 0) + (data.ticket_unread || 0);
                if (prevUnread !== null && unread > prevUnread && Date.now() - lastRing > 8000) {
                    lastRing = Date.now();
                    playBell('chat.wav');
                    showToast('New message received', '💬');
                }
                prevUnread = unread;
                updateBellBadge(unread);
            })
            .catch(() => {});
    }

    // Called when a push notification arrives while the app is open.
    window.tbpRingBell = function (payload) {
        if (Date.now() - lastRing < 3000) return;
        lastRing = Date.now();
        playBell('chat.wav');
        const t = document.createElement('span');
        t.textContent = (payload && (payload.title + (payload.body ? ': ' + payload.body : ''))) || 'New message received';
        showToast(t.innerHTML, '💬');
        updateStats();
    };

    updateStats();
    setInterval(updateStats, 10000);
})();
</script>
@include('partials.pwa-admin')
</body>
</html>
