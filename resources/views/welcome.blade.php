<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Astronotify</title>
        
        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased bg-slate-950 text-white min-h-screen flex flex-col items-center justify-center relative overflow-hidden">
        
        <!-- Background Effects -->
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
            <div class="absolute top-[-20%] left-[-10%] w-[50%] h-[50%] bg-purple-900/30 rounded-full blur-[120px]"></div>
            <div class="absolute bottom-[-20%] right-[-10%] w-[60%] h-[60%] bg-blue-900/20 rounded-full blur-[150px]"></div>
        </div>

        <div class="relative z-10 w-full max-w-7xl mx-auto px-6 lg:px-8 flex flex-col items-center text-center">
            
            <!-- Logo -->
            <div class="mb-8">
                <x-application-mark class="w-32 h-32" />
            </div>

            <!-- Hero Section -->
            <h1 class="text-5xl md:text-7xl font-extrabold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-500 mb-6">
                Never Miss a Flawless Night Sky
            </h1>
            
            <p class="text-xl text-slate-400 max-w-3xl mb-12">
                Astronotify is your automated personal astronomy aide. Register your locations, define your exact weather thresholds, and get notified exactly when perfect conditions align. 
            </p>

            <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-6">
                @auth
                    <a href="{{ route('dashboard') }}" class="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-500 hover:to-purple-500 text-white font-bold py-4 px-10 rounded-2xl shadow-lg transition-transform transform hover:scale-105 text-lg flex items-center justify-center">
                        Go to Dashboard
                    </a>
                @else
                    <a href="{{ route('register') }}" class="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-500 hover:to-purple-500 text-white font-bold py-4 px-10 rounded-2xl shadow-lg transition-transform transform hover:scale-105 text-lg flex items-center justify-center">
                        Get Started
                    </a>
                    <a href="{{ route('login') }}" class="bg-slate-800/80 hover:bg-slate-700 border border-slate-600 text-white font-bold py-4 px-10 rounded-2xl shadow-lg transition-transform transform hover:scale-105 text-lg backdrop-blur-md flex items-center justify-center">
                        Log In
                    </a>
                @endauth
            </div>
            
            <!-- Features Row -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-24">
                <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-8 backdrop-blur-xl">
                    <div class="text-blue-400 mb-4">
                        <svg class="w-10 h-10 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Track Any Location</h3>
                    <p class="text-slate-400">Save multiple stargazing spots using highly specific Geocoding coordinates.</p>
                </div>
                <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-8 backdrop-blur-xl">
                    <div class="text-purple-400 mb-4">
                        <svg class="w-10 h-10 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" /></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Granular Weather Checks</h3>
                    <p class="text-slate-400">Control thresholds for minimum clear night hours, wind speeds, and cloud volumes.</p>
                </div>
                <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-8 backdrop-blur-xl">
                    <div class="text-emerald-400 mb-4">
                        <svg class="w-10 h-10 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Automated Email Alerts</h3>
                    <p class="text-slate-400">Our system polls the daily forecast. When exactly specific conditions align perfectly, you are instantly informed.</p>
                </div>
            </div>

        </div>
        <x-footer />
    </body>
</html>
