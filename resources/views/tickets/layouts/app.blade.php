<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Support') — TravelBookingPanel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>* { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="min-h-screen bg-slate-50 antialiased">
    <nav class="bg-white border-b border-slate-100 shadow-sm sticky top-0 z-40">
        <div class="max-w-4xl mx-auto px-4 h-14 flex items-center justify-between">
            <a href="{{ route('tickets.index') }}" class="flex items-center gap-2">
                <div class="w-7 h-7 bg-blue-600 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                </div>
                <span class="font-semibold text-slate-800 text-sm">Support Center</span>
            </a>

            <div class="flex items-center gap-3">
                @if(auth('ticket_user')->check())
                    <a href="{{ route('tickets.create') }}" class="text-sm text-blue-600 hover:underline font-medium">New Ticket</a>
                    <a href="{{ route('tickets.profile') }}" class="text-sm text-slate-600 hover:text-slate-900 font-medium">Profile</a>
                    <form method="POST" action="{{ route('tickets.logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-slate-500 hover:text-slate-700 cursor-pointer">Logout</button>
                    </form>
                @else
                    <a href="{{ route('tickets.login') }}" class="text-sm text-slate-600 hover:text-slate-900">Login</a>
                    <a href="{{ route('tickets.register') }}" class="text-sm bg-blue-600 text-white px-3 py-1.5 rounded-lg hover:bg-blue-700 transition-colors">Register</a>
                @endif
            </div>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto px-4 py-8">
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">{{ session('error') }}</div>
        @endif
        @yield('content')
    </main>
</body>
</html>
