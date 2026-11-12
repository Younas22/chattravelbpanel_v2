{{-- Settings sub-navigation: Widget · Pusher · Profile --}}
@php
    $settingsTabs = [
        ['admin.settings.widget',  'Widget',  'M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z'],
        ['admin.settings.pusher',  'Pusher',  'M13 10V3L4 14h7v7l9-11h-7z'],
        ['admin.settings.profile', 'Profile', 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
    ];
@endphp
<nav class="mb-6 border-b border-slate-200" aria-label="Settings">
    <div class="-mb-px flex gap-1 overflow-x-auto thin-scroll">
        @foreach($settingsTabs as [$route, $label, $icon])
            @php $active = request()->routeIs($route . '*'); @endphp
            <a href="{{ route($route) }}" @if($active) aria-current="page" @endif
               class="inline-flex shrink-0 items-center gap-2 border-b-2 px-4 py-2.5 text-sm font-medium transition-colors
                      {{ $active ? 'border-brand-600 text-brand-700' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/></svg>
                {{ $label }}
            </a>
        @endforeach
    </div>
</nav>
