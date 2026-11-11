{{-- Customer portal top bar (shared by tickets.layouts.app and tickets.layouts.chat) --}}
@php
    $navLinks = auth('ticket_user')->check() ? [
        ['route' => 'tickets.chat.index',  'label' => 'Chat',       'active' => request()->routeIs('tickets.chat*')],
        ['route' => 'tickets.index',       'label' => 'My Tickets', 'active' => request()->routeIs('tickets.index', 'tickets.show')],
        ['route' => 'tickets.create',      'label' => 'New Ticket', 'active' => request()->routeIs('tickets.create')],
        ['route' => 'tickets.profile',     'label' => 'Profile',    'active' => request()->routeIs('tickets.profile')],
    ] : [];
@endphp
<nav class="bg-white/90 backdrop-blur border-b border-slate-100 sticky top-0 z-40 shrink-0" x-data="{ menu: false }">
    <div class="{{ $wide ?? false ? '' : 'max-w-4xl mx-auto' }} px-3 sm:px-4 h-14 flex items-center gap-2">
        <a href="{{ route('tickets.index') }}" class="flex items-center gap-2 mr-auto min-w-0">
            <div class="w-8 h-8 bg-brand-600 rounded-xl grid place-items-center shrink-0">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
            </div>
            <span class="font-semibold text-slate-800 text-sm truncate">Support Center</span>
        </a>

        @if(auth('ticket_user')->check())
            {{-- Desktop links --}}
            <div class="hidden md:flex items-center gap-1">
                @foreach($navLinks as $l)
                    <a href="{{ route($l['route']) }}"
                       class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors {{ $l['active'] ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">{{ $l['label'] }}</a>
                @endforeach
                <form method="POST" action="{{ route('tickets.logout') }}">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 rounded-lg text-sm text-slate-500 hover:text-red-600 hover:bg-red-50 transition-colors cursor-pointer">Logout</button>
                </form>
            </div>
        @else
            <a href="{{ route('tickets.login') }}" class="px-3 py-1.5 text-sm text-slate-600 hover:text-slate-900">Login</a>
            <a href="{{ route('tickets.register') }}" class="btn-primary !py-1.5 !px-3">Register</a>
        @endif

        @include('partials.theme-picker')

        @if(auth('ticket_user')->check())
            <button type="button" @click="menu = !menu" class="icon-btn md:hidden" aria-label="Menu" :aria-expanded="menu">
                <svg x-show="!menu" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <svg x-show="menu" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        @endif
    </div>

    @if(auth('ticket_user')->check())
        {{-- Mobile menu --}}
        <div x-show="menu" x-cloak x-transition @click.outside="menu = false" class="md:hidden border-t border-slate-100 bg-white px-3 py-2 space-y-1 shadow-lg">
            @foreach($navLinks as $l)
                <a href="{{ route($l['route']) }}"
                   class="block px-3 py-2.5 rounded-xl text-sm font-medium {{ $l['active'] ? 'bg-brand-50 text-brand-700' : 'text-slate-700 hover:bg-slate-50' }}">{{ $l['label'] }}</a>
            @endforeach
            <form method="POST" action="{{ route('tickets.logout') }}">
                @csrf
                <button type="submit" class="w-full text-left px-3 py-2.5 rounded-xl text-sm font-medium text-red-600 hover:bg-red-50 cursor-pointer">Logout</button>
            </form>
        </div>
    @endif
</nav>
