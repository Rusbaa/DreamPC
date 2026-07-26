@extends('layouts.app')

@section('content')
<div class="w-full max-w-md bg-slate-900/80 border border-slate-800 rounded-2xl p-8 shadow-2xl backdrop-blur-xl">
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-bold text-white">Welcome Back</h2>
        <p class="text-sm text-slate-400 mt-1">Sign in to your DreamPC account</p>
    </div>

    @if ($errors->any())
        <div class="mb-4 bg-red-500/10 border border-red-500/20 text-red-400 p-3 rounded-lg text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="/login" class="space-y-4">
        @csrf
        <div>
            <label for="email" class="block text-xs font-medium text-slate-400 mb-1">Email Address</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                class="w-full bg-slate-950 border border-slate-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-600 outline-none transition">
        </div>

        <div>
            <label for="password" class="block text-xs font-medium text-slate-400 mb-1">Password</label>
            <input type="password" name="password" id="password" required
                class="w-full bg-slate-950 border border-slate-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-600 outline-none transition">
        </div>

        <div class="flex items-center justify-between text-xs">
            <label class="flex items-center text-slate-400">
                <input type="checkbox" name="remember" class="rounded border-slate-800 bg-slate-950 text-blue-600 focus:ring-blue-500 mr-2">
                Remember me
            </label>
        </div>

        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-medium py-2.5 rounded-lg text-sm transition shadow-lg shadow-blue-600/25 mt-2">
            Sign In
        </button>
    </form>

    <div class="mt-6 text-center text-xs text-slate-400">
        Don't have an account? <a href="/register" class="text-blue-400 hover:underline">Register</a>
    </div>
</div>
@endsection
