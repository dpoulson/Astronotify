<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Astronotify — Automated Stargazing Alerts &amp; ISS Transit Predictions</title>
        <meta name="description" content="Personal automated stargazing assistant. Track weather thresholds, ISS solar/lunar transits, moon phases, and light pollution for your custom observing locations.">
        
        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased bg-slate-950 text-white min-h-screen flex flex-col font-sans selection:bg-purple-500 selection:text-white relative overflow-x-hidden">
        
        <!-- Ambient Background Glows -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none z-0">
            <div class="absolute top-[-15%] left-[-10%] w-[55vw] h-[55vw] bg-purple-900/20 rounded-full blur-[140px]"></div>
            <div class="absolute top-[30%] right-[-15%] w-[50vw] h-[50vw] bg-blue-900/15 rounded-full blur-[160px]"></div>
            <div class="absolute bottom-[-10%] left-[20%] w-[45vw] h-[45vw] bg-indigo-900/20 rounded-full blur-[150px]"></div>
        </div>

        <!-- Sticky Header Navigation -->
        <header class="sticky top-0 z-50 border-b border-slate-800/80 bg-slate-950/75 backdrop-blur-xl transition-all">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                    <x-application-mark class="h-9 w-auto transform group-hover:scale-105 transition-transform" />
                    <span class="font-extrabold text-xl tracking-tight text-white">Astronotify</span>
                </a>

                <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-300">
                    <a href="#features" class="hover:text-purple-300 transition-colors">Features</a>
                    <a href="#transits" class="hover:text-purple-300 transition-colors">ISS Transits</a>
                    <a href="#how-it-works" class="hover:text-purple-300 transition-colors">How It Works</a>
                    <a href="{{ route('about') }}" class="hover:text-purple-300 transition-colors">About</a>
                    <a href="{{ route('faq') }}" class="hover:text-purple-300 transition-colors">Help &amp; FAQ</a>
                </nav>

                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="px-5 py-2 rounded-xl text-xs sm:text-sm font-bold bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-500 hover:to-purple-500 text-white shadow-lg shadow-purple-900/30 transition-all transform hover:scale-105">
                            Open Dashboard &rarr;
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold text-slate-300 hover:text-white hover:bg-slate-900 transition-all">
                            Log In
                        </a>
                        <a href="{{ route('register') }}" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-500 hover:to-purple-500 text-white shadow-lg shadow-purple-900/30 transition-all transform hover:scale-105">
                            Get Started Free
                        </a>
                    @endauth
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="relative z-10 flex-grow">
            <!-- Hero Section -->
            <section class="pt-16 pb-20 md:pt-24 md:pb-28 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center flex flex-col items-center">
                <!-- Badge -->
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-purple-950/70 border border-purple-700/60 text-purple-300 text-xs font-semibold mb-6 shadow-inner">
                    <span class="w-2 h-2 rounded-full bg-purple-400 animate-pulse"></span>
                    <span>Built for Amateur Astronomers &amp; Astrophotographers</span>
                </div>

                <!-- Headline -->
                <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black tracking-tight max-w-4xl text-transparent bg-clip-text bg-gradient-to-r from-blue-400 via-purple-300 to-amber-300 mb-6 leading-[1.1]">
                    Never Miss a Flawless Night Sky or Rare Transit
                </h1>

                <!-- Subtitle -->
                <p class="text-base sm:text-xl text-slate-300 max-w-2xl mb-10 leading-relaxed font-normal">
                    Automated personal stargazing intelligence. Register your observing sites, tune your exact cloud and wind limits, and get notified when pristine dark skies or ISS solar/lunar transits align.
                </p>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center w-full max-w-md">
                    @auth
                        <a href="{{ route('dashboard') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-2xl bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-500 hover:to-purple-500 text-white font-bold text-base shadow-xl shadow-purple-900/40 transition-all transform hover:scale-105 flex items-center justify-center gap-2">
                            <span>Go to Your Dashboard</span>
                            <span>&rarr;</span>
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-2xl bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-500 hover:to-purple-500 text-white font-bold text-base shadow-xl shadow-purple-900/40 transition-all transform hover:scale-105 flex items-center justify-center gap-2">
                            <span>Start Stargazing Free</span>
                            <span>&rarr;</span>
                        </a>
                        <a href="{{ route('faq') }}" class="w-full sm:w-auto px-6 py-3.5 rounded-2xl bg-slate-900/80 hover:bg-slate-800 border border-slate-700/80 text-slate-300 hover:text-white font-semibold text-base backdrop-blur-md transition-all">
                            How It Works
                        </a>
                    @endauth
                </div>

                <!-- Showcase Preview Grid -->
                <div class="mt-16 sm:mt-20 w-full max-w-5xl grid grid-cols-1 lg:grid-cols-12 gap-6 text-left">
                    <!-- Left Showcase Card: ISS Solar Transit Simulation -->
                    <div id="transits" class="lg:col-span-7 bg-slate-900/70 border border-amber-500/30 rounded-3xl p-6 sm:p-8 backdrop-blur-xl shadow-2xl relative overflow-hidden flex flex-col justify-between">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-1 rounded-full bg-amber-950/80 text-amber-300 border border-amber-700 text-xs font-bold uppercase tracking-wider">
                                    ☀️ Solar Transit Preview
                                </span>
                                <span class="text-xs font-bold px-2 py-0.5 rounded bg-green-950 text-green-300 border border-green-700">✓ Exact Transit</span>
                            </div>
                            <span class="text-xs text-slate-400 font-mono">Separation: 0.12&deg;</span>
                        </div>

                        <!-- Mini SVG Visualizer -->
                        <div class="relative w-full bg-slate-950/90 rounded-2xl p-4 flex items-center justify-center border border-slate-800/80 overflow-hidden my-3">
                            <svg viewBox="0 0 280 180" class="w-full max-w-[280px]">
                                <defs>
                                    <radialGradient id="preview-sun" cx="40%" cy="35%">
                                        <stop offset="0%" stop-color="#fef08a"/>
                                        <stop offset="50%" stop-color="#fbbf24"/>
                                        <stop offset="85%" stop-color="#ea580c"/>
                                        <stop offset="100%" stop-color="#b45309" stop-opacity="0.7"/>
                                    </radialGradient>
                                    <filter id="preview-glow">
                                        <feGaussianBlur stdDeviation="4" result="b"/>
                                        <feMerge><feMergeNode in="b"/><feMergeNode in="SourceGraphic"/></feMerge>
                                    </filter>
                                </defs>
                                <circle cx="140" cy="90" r="50" fill="url(#preview-sun)" filter="url(#preview-glow)"/>
                                <circle cx="140" cy="90" r="50" fill="none" stroke="#f59e0b" stroke-width="1.5" stroke-opacity="0.8"/>
                                <!-- Crosshairs -->
                                <line x1="80" y1="90" x2="200" y2="90" stroke="#475569" stroke-width="0.7" stroke-dasharray="2 3"/>
                                <line x1="140" y1="30" x2="140" y2="150" stroke="#475569" stroke-width="0.7" stroke-dasharray="2 3"/>
                                <!-- ISS trajectory chord -->
                                <line x1="40" y1="120" x2="240" y2="60" stroke="#c084fc" stroke-width="2.5" stroke-dasharray="3 3"/>
                                <circle cx="130" cy="93" r="4.5" fill="#f3e8ff" stroke="#9333ea" stroke-width="1.5"/>
                            </svg>
                            <div class="absolute bottom-2 right-3 text-[10px] text-purple-300 font-mono">0.96s crossing duration</div>
                        </div>

                        <div class="pt-3 border-t border-slate-800 flex items-center justify-between text-xs">
                            <span class="text-slate-400">Predicted for: <strong>Home Observatory</strong></span>
                            <span class="px-2 py-0.5 rounded bg-emerald-950/70 border border-emerald-700/60 text-emerald-300 font-semibold">🌤️ 15% Cloud (Favorable)</span>
                        </div>
                    </div>

                    <!-- Right Showcase Card: Stargazing Forecast -->
                    <div class="lg:col-span-5 bg-slate-900/70 border border-purple-500/30 rounded-3xl p-6 sm:p-8 backdrop-blur-xl shadow-2xl flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <span class="px-2.5 py-1 rounded-full bg-purple-950/80 text-purple-300 border border-purple-700 text-xs font-bold uppercase tracking-wider">
                                    🌌 Dark Sky Window
                                </span>
                                <span class="text-xs text-emerald-400 font-bold">Optimal Night</span>
                            </div>

                            <div class="space-y-3">
                                <div class="bg-slate-950/80 p-3.5 rounded-xl border border-slate-800">
                                    <div class="flex items-center justify-between text-xs mb-1">
                                        <span class="text-slate-400">Moon Phase</span>
                                        <span class="text-slate-200 font-semibold">🌑 New Moon (4% Illum.)</span>
                                    </div>
                                    <div class="text-[11px] text-purple-300">Pristine dark sky window for deep-sky imaging</div>
                                </div>

                                <div class="bg-slate-950/80 p-3.5 rounded-xl border border-slate-800">
                                    <div class="flex items-center justify-between text-xs mb-1">
                                        <span class="text-slate-400">Consecutive Clear Window</span>
                                        <span class="text-white font-bold">5.5 Hours</span>
                                    </div>
                                    <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden mt-1.5">
                                        <div class="bg-gradient-to-r from-emerald-500 to-blue-500 h-full w-[85%] rounded-full"></div>
                                    </div>
                                </div>

                                <div class="bg-slate-950/80 p-3.5 rounded-xl border border-slate-800 flex justify-between items-center text-xs">
                                    <span class="text-slate-400">Bortle Scale</span>
                                    <span class="font-bold text-amber-300">Class 3 (Rural Sky)</span>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-800 text-[11px] text-slate-400 text-center">
                            Deduplicated daily alerts delivered to your inbox at midnight.
                        </div>
                    </div>
                </div>
            </section>

            <!-- Features Grid Section -->
            <section id="features" class="py-20 border-t border-slate-900 bg-slate-950/50">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center max-w-3xl mx-auto mb-16">
                        <span class="text-xs uppercase font-extrabold tracking-widest text-purple-400 bg-purple-950/60 border border-purple-800/60 px-3 py-1 rounded-full inline-block mb-3">Engineered for Astronomy</span>
                        <h2 class="text-3xl sm:text-5xl font-extrabold text-white mb-4">
                            Precision Tools Without the Clutter
                        </h2>
                        <p class="text-slate-400 text-base sm:text-lg">
                            Stop refreshing ten different weather apps. Astronotify automates the calculations and watches the skies for you.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        <!-- Feature 1 -->
                        <div class="bg-slate-900/50 border border-slate-800/80 rounded-3xl p-8 hover:border-purple-500/40 transition-all hover:-translate-y-1">
                            <div class="w-12 h-12 rounded-2xl bg-purple-950/80 border border-purple-700/60 flex items-center justify-center text-2xl mb-6">
                                🛰️
                            </div>
                            <h3 class="text-xl font-bold text-white mb-2">ISS Solar &amp; Lunar Transits</h3>
                            <p class="text-sm text-slate-400 leading-relaxed">
                                High-precision SGP4 orbital propagation computes when the ISS crosses the disc of the Sun or Moon from your exact GPS coordinates with 2-second chord density.
                            </p>
                        </div>

                        <!-- Feature 2 -->
                        <div class="bg-slate-900/50 border border-slate-800/80 rounded-3xl p-8 hover:border-purple-500/40 transition-all hover:-translate-y-1">
                            <div class="w-12 h-12 rounded-2xl bg-blue-950/80 border border-blue-700/60 flex items-center justify-center text-2xl mb-6">
                                🌤️
                            </div>
                            <h3 class="text-xl font-bold text-white mb-2">Granular Weather Filters</h3>
                            <p class="text-sm text-slate-400 leading-relaxed">
                                Define exact tolerances for cloud cover percentage, maximum wind speeds in km/h, and minimum continuous clear sky hours before a night qualifies.
                            </p>
                        </div>

                        <!-- Feature 3 -->
                        <div class="bg-slate-900/50 border border-slate-800/80 rounded-3xl p-8 hover:border-purple-500/40 transition-all hover:-translate-y-1">
                            <div class="w-12 h-12 rounded-2xl bg-amber-950/80 border border-amber-700/60 flex items-center justify-center text-2xl mb-6">
                                🌑
                            </div>
                            <h3 class="text-xl font-bold text-white mb-2">Moon Phase &amp; Dark Sky Tracking</h3>
                            <p class="text-sm text-slate-400 leading-relaxed">
                                Calculates exact lunar illumination fractions. Highlights when skies are moon-free for deep-sky imaging, or bright for high-power lunar crater study.
                            </p>
                        </div>

                        <!-- Feature 4 -->
                        <div class="bg-slate-900/50 border border-slate-800/80 rounded-3xl p-8 hover:border-purple-500/40 transition-all hover:-translate-y-1">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-950/80 border border-emerald-700/60 flex items-center justify-center text-2xl mb-6">
                                🌌
                            </div>
                            <h3 class="text-xl font-bold text-white mb-2">Bortle Scale Light Pollution</h3>
                            <p class="text-sm text-slate-400 leading-relaxed">
                                Records Bortle ratings from Class 1 (pristine dark sky) to Class 9 (inner-city skyglow) for each site, with direct links to geographical light pollution maps.
                            </p>
                        </div>

                        <!-- Feature 5 -->
                        <div class="bg-slate-900/50 border border-slate-800/80 rounded-3xl p-8 hover:border-purple-500/40 transition-all hover:-translate-y-1">
                            <div class="w-12 h-12 rounded-2xl bg-pink-950/80 border border-pink-700/60 flex items-center justify-center text-2xl mb-6">
                                🔕
                            </div>
                            <h3 class="text-xl font-bold text-white mb-2">Intelligent Zero-Spam Alerts</h3>
                            <p class="text-sm text-slate-400 leading-relaxed">
                                Automated deduplication prevents repeated emails when forecasts fluctuate. Transits with &ge; 90% cloud cover are automatically silenced until weather clears.
                            </p>
                        </div>

                        <!-- Feature 6 -->
                        <div class="bg-slate-900/50 border border-slate-800/80 rounded-3xl p-8 hover:border-purple-500/40 transition-all hover:-translate-y-1">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-950/80 border border-indigo-700/60 flex items-center justify-center text-2xl mb-6">
                                📍
                            </div>
                            <h3 class="text-xl font-bold text-white mb-2">Elevation &amp; Multi-Site Precision</h3>
                            <p class="text-sm text-slate-400 leading-relaxed">
                                Track your backyard, club observatory, and remote dark-sky trip sites independently. Elevation support accounts for critical parallax offsets during satellite passes.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- How It Works Section -->
            <section id="how-it-works" class="py-20 border-t border-slate-900">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center max-w-3xl mx-auto mb-16">
                        <span class="text-xs uppercase font-extrabold tracking-widest text-purple-400 bg-purple-950/60 border border-purple-800/60 px-3 py-1 rounded-full inline-block mb-3">Simple 3-Step Setup</span>
                        <h2 class="text-3xl sm:text-5xl font-extrabold text-white mb-4">
                            How Astronotify Works
                        </h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
                        <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-8 relative">
                            <div class="text-purple-400 font-extrabold text-3xl mb-4">01</div>
                            <h3 class="text-xl font-bold text-white mb-2">Add Your Locations</h3>
                            <p class="text-sm text-slate-400 leading-relaxed">
                                Enter your exact GPS latitude, longitude, and elevation. Save your home garden, favorite hilltop, or holiday dark-sky spot.
                            </p>
                        </div>

                        <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-8 relative">
                            <div class="text-purple-400 font-extrabold text-3xl mb-4">02</div>
                            <h3 class="text-xl font-bold text-white mb-2">Set Your Tolerances</h3>
                            <p class="text-sm text-slate-400 leading-relaxed">
                                Specify how much cloud or wind your equipment tolerates, the minimum clear hours you need, and toggle ISS transit predictions on or off.
                            </p>
                        </div>

                        <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-8 relative">
                            <div class="text-purple-400 font-extrabold text-3xl mb-4">03</div>
                            <h3 class="text-xl font-bold text-white mb-2">Get Actionable Alerts</h3>
                            <p class="text-sm text-slate-400 leading-relaxed">
                                Our engine calculates orbits and analyzes daily forecasts. When conditions match your criteria, you get a clean summary email so you can set up your telescope.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- CTA Banner -->
            <section class="py-16 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-gradient-to-r from-blue-900/40 via-purple-900/40 to-slate-900/80 border border-purple-500/30 rounded-3xl p-8 sm:p-12 text-center relative overflow-hidden backdrop-blur-xl shadow-2xl">
                    <div class="absolute -right-16 -top-16 w-64 h-64 bg-purple-600/10 rounded-full blur-3xl pointer-events-none"></div>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-white mb-4">
                        Ready to Catch the Next Clear Sky?
                    </h2>
                    <p class="text-slate-300 text-sm sm:text-base max-w-xl mx-auto mb-8">
                        Sign up in 30 seconds. No credit card required. Free and open source for stargazers everywhere.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="{{ route('register') }}" class="px-8 py-3.5 rounded-2xl bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-500 hover:to-purple-500 text-white font-bold text-base shadow-xl shadow-purple-900/40 transition-all transform hover:scale-105">
                            Create Your Free Account
                        </a>
                        <a href="{{ route('faq') }}" class="px-6 py-3.5 rounded-2xl bg-slate-900/90 hover:bg-slate-800 border border-slate-700 text-slate-300 hover:text-white font-semibold text-base transition-all">
                            Read the FAQ
                        </a>
                    </div>
                </div>
            </section>
        </main>

        <x-footer />
    </body>
</html>
