@extends('tickets.layouts.app')
@section('title', 'Login')

@section('content')
<div class="max-w-sm mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
        <h1 class="text-xl font-bold text-slate-900 mb-1">Sign in</h1>
        <p class="text-sm text-slate-500 mb-6">Access your support tickets</p>

        @if($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-100 text-red-600 text-sm rounded-xl">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('tickets.login.post') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Password</label>
                <input type="password" name="password" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="w-full py-2.5 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition-colors text-sm">Sign in</button>
        </form>

        <p class="text-center text-sm text-slate-500 mt-4">
            Don't have an account? <a href="{{ route('tickets.register') }}" class="text-blue-600 hover:underline font-medium">Register</a>
        </p>
    </div>
</div>
@endsection
