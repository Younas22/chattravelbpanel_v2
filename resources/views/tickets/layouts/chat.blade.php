<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Chat') — TravelBookingPanel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>* { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="h-screen bg-slate-50 antialiased overflow-hidden">
    <div class="flex flex-col h-screen">
        <nav class="bg-white border-b border-slate-100 shadow-sm shrink-0">
            <div class="px-4 h-14 flex items-center justify-between">
                <a href="{{ route('tickets.index') }}" class="flex items-center gap-2">
                    <div class="w-7 h-7 bg-blue-600 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                    </div>
                    <span class="font-semibold text-slate-800 text-sm">Support Center</span>
                </a>

                <div class="flex items-center gap-3">
                    <a href="{{ route('tickets.chat.index') }}" class="text-sm text-blue-600 font-medium">Chat</a>
                    <a href="{{ route('tickets.create') }}" class="text-sm text-slate-600 hover:text-slate-900 font-medium">New Ticket</a>
                    <a href="{{ route('tickets.profile') }}" class="text-sm text-slate-600 hover:text-slate-900 font-medium">Profile</a>
                    <form method="POST" action="{{ route('tickets.logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-slate-500 hover:text-slate-700 cursor-pointer">Logout</button>
                    </form>
                </div>
            </div>
        </nav>

        <main class="flex-1 overflow-hidden p-4">
            @yield('content')
        </main>
    </div>

@stack('scripts')
</body>
</html>
