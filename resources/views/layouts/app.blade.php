<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'DreamPC') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col antialiased">
    <nav class="border-b border-slate-800 bg-slate-900/50 backdrop-blur-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="/" class="text-xl font-bold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-indigo-500">
                DreamPC
            </a>
            <div class="flex items-center space-x-4">
                @auth
                    <span class="text-sm text-slate-400">Welcome, {{ Auth::user()->name }}</span>
                    @if(Auth::user()->role === 'admin')
                        <a href="/admin/dashboard" class="text-xs bg-indigo-600/30 text-indigo-400 border border-indigo-500/30 px-2.5 py-1 rounded-full font-medium">Admin</a>
                    @endif
                    <form method="POST" action="/logout" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-slate-400 hover:text-white transition">Logout</button>
                    </form>
                @else
                    <a href="/login" class="text-sm text-slate-300 hover:text-white transition">Log in</a>
                    <a href="/register" class="text-sm bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-lg font-medium transition shadow-lg shadow-blue-600/20">Register</a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="flex-grow flex items-center justify-center p-6">
        @yield('content')
    </main>
</body>
</html>
