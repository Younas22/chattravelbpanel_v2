{{-- Installable app (PWA) - included in the admin layout and the admin login page --}}
@php $pwaName = \App\Models\WidgetSetting::get('system_name', 'TBP Chat'); @endphp
<link rel="manifest" href="{{ route('pwa.manifest') }}">
<meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">
<meta name="theme-color" content="#0f172a" media="(prefers-color-scheme: dark)">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="{{ $pwaName }}">
<meta name="application-name" content="{{ $pwaName }}">
<link rel="apple-touch-icon" href="{{ \App\Services\PwaIcons::url('apple-touch-icon.png') }}">
<script>
    window.TBP_PWA = {
        swUrl: @json(route('pwa.sw')),
        scope: @json(rtrim(url('/admin'), '/') . '/'),
        standalone: window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true,
    };
    if (window.TBP_PWA.standalone) document.documentElement.classList.add('is-pwa');
    if ('serviceWorker' in navigator) {
        window.TBP_PWA.ready = navigator.serviceWorker
            .register(window.TBP_PWA.swUrl, { scope: window.TBP_PWA.scope })
            .catch(function (e) { console.warn('SW registration failed', e); });
    }
</script>
