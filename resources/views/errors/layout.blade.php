<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - Astronotify</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #020617;
            background-image: 
                radial-gradient(at 0% 0%, rgba(124, 58, 237, 0.08) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(59, 130, 246, 0.08) 0px, transparent 50%),
                radial-gradient(at 50% 100%, rgba(147, 51, 234, 0.05) 0px, transparent 50%);
        }
    </style>
</head>
<body class="min-h-screen text-slate-100 flex flex-col items-center justify-center p-4 relative overflow-hidden select-none">
    <!-- Starfield backgrounds -->
    <div class="absolute inset-0 opacity-25 pointer-events-none">
        <div class="absolute w-1 h-1 bg-white rounded-full top-10 left-1/4 animate-ping" style="animation-duration: 3s;"></div>
        <div class="absolute w-1 h-1 bg-white rounded-full top-1/3 left-3/4 animate-ping" style="animation-duration: 4s;"></div>
        <div class="absolute w-1.5 h-1.5 bg-indigo-300 rounded-full top-2/3 left-1/5 animate-ping" style="animation-duration: 5s;"></div>
        <div class="absolute w-1 h-1 bg-white rounded-full top-3/4 left-2/3 animate-ping" style="animation-duration: 3.5s;"></div>
        
        <div class="absolute w-0.5 h-0.5 bg-slate-400 rounded-full top-1/4 left-1/2"></div>
        <div class="absolute w-0.5 h-0.5 bg-slate-500 rounded-full top-1/2 left-1/3"></div>
        <div class="absolute w-0.5 h-0.5 bg-slate-400 rounded-full top-2/3 left-2/3"></div>
        <div class="absolute w-0.5 h-0.5 bg-slate-500 rounded-full top-1/6 left-5/6"></div>
    </div>

    <div class="max-w-md w-full text-center space-y-8 z-10">
        <!-- Orbiting animation graphic -->
        <div class="relative w-32 h-32 mx-auto flex items-center justify-center">
            <div class="absolute inset-0 border border-slate-700/60 rounded-full animate-spin" style="animation-duration: 8s;"></div>
            <!-- Orbiting satellite -->
            <div class="absolute w-3 h-3 bg-purple-500 rounded-full top-0 left-1/2 -ml-1.5 shadow-[0_0_10px_#a855f7]"></div>
            
            <!-- Center body -->
            <div class="w-16 h-16 rounded-full bg-gradient-to-tr from-blue-600 to-purple-600 flex items-center justify-center text-2xl shadow-[0_0_25px_rgba(124,58,237,0.35)] font-black text-white">
                @yield('code')
            </div>
        </div>

        <div class="space-y-4">
            <h1 class="text-3xl font-extrabold text-white tracking-tight">
                @yield('title')
            </h1>
            <p class="text-slate-400 text-sm max-w-sm mx-auto leading-relaxed">
                @yield('message')
            </p>
        </div>

        <div class="pt-2">
            <a href="{{ url('/') }}" class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-500 hover:to-purple-500 text-white font-bold text-sm rounded-xl shadow-lg transition-all transform hover:scale-105">
                🌌 Return to Base
            </a>
        </div>
    </div>
</body>
</html>
