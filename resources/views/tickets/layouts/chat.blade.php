<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Chat') — TravelBookingPanel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @include('partials.theme-head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-dvh bg-slate-50 antialiased overflow-hidden">
    <div class="flex flex-col h-dvh">
        @include('tickets.partials.nav', ['wide' => true])

        <main class="flex-1 min-h-0 overflow-hidden lg:p-4">
            @yield('content')
        </main>
    </div>

@stack('scripts')
</body>
</html>
