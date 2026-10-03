<x-slot name="title">{{ $spot->name }} — Dark Sky Stargazing Forecast &amp; ISS Transits</x-slot>

@push('meta')
    <meta name="description" content="Check live stargazing conditions, Bortle {{ $spot->bortle_class }} dark sky ratings, night cloud forecasts, and ISS transits for {{ $spot->name }} in {{ $spot->region ? $spot->region . ', ' : '' }}{{ $spot->country }}.">
    <meta property="og:title" content="{{ $spot->name }} — Stargazing &amp; Dark Sky Forecast">
    <meta property="og:description" content="Bortle {{ $spot->bortle_class }} sky rating with live cloud forecasts and ISS transit predictions over {{ $spot->name }}. Zero tracking, free via Astronotify.">
    <meta property="og:type" content="place">
    <meta property="og:url" content="{{ route('spots.show', $spot->slug) }}">
    <meta property="og:image" content="{{ asset('images/og-card.png') }}">
    <link rel="canonical" href="{{ route('spots.show', $spot->slug) }}">

    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "TouristAttraction",
        "name": "{{ $spot->name }}",
        "description": "{{ addslashes($spot->description) }}",
        "url": "{{ route('spots.show', $spot->slug) }}",
        "geo": {
            "@@type": "GeoCoordinates",
            "latitude": {{ $spot->latitude }},
            "longitude": {{ $spot->longitude }},
            "elevation": {{ $spot->elevation }}
        },
        "address": {
            "@@type": "PostalAddress",
            "addressRegion": "{{ $spot->region }}",
            "addressCountry": "{{ $spot->country }}"
        }
    }
    </script>
@endpush

<div class="min-h-screen bg-slate-950 text-white selection:bg-purple-500 selection:text-white">
    <!-- Spot Header / Hero -->
    <div class="relative overflow-hidden pt-12 pb-14 border-b border-slate-800">
        <div class="absolute inset-0 pointer-events-none opacity-40">
            <div class="absolute top-[-20%] left-[20%] w-[50%] h-[50%] bg-purple-900/25 rounded-full blur-[160px]"></div>
            <div class="absolute bottom-[-10%] right-[10%] w-[45%] h-[45%] bg-blue-900/20 rounded-full blur-[140px]"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <!-- Breadcrumbs -->
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-6 font-medium">
                <a href="{{ url('/') }}" class="hover:text-purple-400 transition">Home</a>
                <span>/</span>
                <a href="{{ route('spots.index') }}" class="hover:text-purple-400 transition">Stargazing Spots</a>
                <span>/</span>
                <span class="text-slate-200">{{ $spot->name }}</span>
            </div>

            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div>
                    <div class="flex flex-wrap items-center gap-2.5 mb-3">
                        <span class="px-3 py-1 rounded-xl text-xs font-bold border {{ $spot->bortle_color }}">
                            Bortle {{ $spot->bortle_class }}
                        </span>
                        @if($spot->dark_sky_status)
                            <span class="px-3 py-1 rounded-xl text-xs font-semibold bg-indigo-950/80 border border-indigo-500/40 text-indigo-300">
                                ★ {{ $spot->dark_sky_status }}
                            </span>
                        @endif
                        <span class="text-xs text-slate-400 font-medium">
                            📍 {{ $spot->region ? $spot->region . ', ' : '' }}{{ $spot->country }}
                        </span>
                    </div>

                    <h1 class="text-3xl sm:text-5xl font-black tracking-tight text-white">
                        {{ $spot->name }}
                    </h1>
                    <p class="mt-2 text-slate-400 text-sm font-mono">
                        {{ $spot->latitude }}° N, {{ $spot->longitude }}° E &bull; Elevation: {{ $spot->elevation }}m
                    </p>
                </div>

                <!-- Action Button -->
                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    <button 
                        type="button" 
                        wire:click="trackSpotInAccount"
                        class="px-6 py-3 rounded-2xl bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-500 hover:to-blue-500 text-white font-bold text-sm shadow-xl transition active:scale-95 flex items-center gap-2"
                    >
                        <span>🔭</span>
                        <span>{{ $isSavedByUser ? 'View in Dashboard' : 'Track This Spot & Get Alerts' }}</span>
                    </button>
                    <a 
                        href="{{ route('spots.index') }}" 
                        class="px-4 py-3 rounded-2xl bg-slate-900 hover:bg-slate-800 border border-slate-800 text-xs font-semibold text-slate-300 transition"
                    >
                        &larr; All Spots
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-12">
        <!-- Overview & Description -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">
                <div class="p-6 sm:p-8 rounded-3xl bg-slate-900/70 border border-slate-800 shadow-xl space-y-4">
                    <h2 class="text-xl font-bold text-white flex items-center gap-2">
                        <span>🌌</span> About This Observing Location
                    </h2>
                    <p class="text-slate-300 text-sm sm:text-base leading-relaxed">
                        {{ $spot->description }}
                    </p>
                    <div class="pt-4 border-t border-slate-800/80 grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
                        <div class="p-3 rounded-2xl bg-slate-950/60 border border-slate-800">
                            <span class="block text-xs text-slate-400">Bortle Scale</span>
                            <span class="text-lg font-bold text-purple-300">Class {{ $spot->bortle_class }}</span>
                        </div>
                        <div class="p-3 rounded-2xl bg-slate-950/60 border border-slate-800">
                            <span class="block text-xs text-slate-400">Elevation</span>
                            <span class="text-lg font-bold text-blue-300">{{ $spot->elevation }} m</span>
                        </div>
                        <div class="p-3 rounded-2xl bg-slate-950/60 border border-slate-800">
                            <span class="block text-xs text-slate-400">Latitude</span>
                            <span class="text-lg font-bold text-slate-200">{{ round($spot->latitude, 3) }}°</span>
                        </div>
                        <div class="p-3 rounded-2xl bg-slate-950/60 border border-slate-800">
                            <span class="block text-xs text-slate-400">Longitude</span>
                            <span class="text-lg font-bold text-slate-200">{{ round($spot->longitude, 3) }}°</span>
                        </div>
                    </div>
                </div>

                <!-- 4-Night Cloud Forecast -->
                <div class="p-6 sm:p-8 rounded-3xl bg-slate-900/70 border border-slate-800 shadow-xl space-y-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold text-white flex items-center gap-2">
                            <span>☁️</span> 4-Night Stargazing Cloud Forecast
                        </h2>
                        <span class="text-[11px] text-slate-400 font-mono">Updated hourly via Open-Meteo</span>
                    </div>

                    @if(!empty($weatherForecast))
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                            @foreach($weatherForecast as $day)
                                <div class="p-4 rounded-2xl border flex flex-col justify-between {{ $day['color'] }} transition-transform hover:-translate-y-0.5">
                                    <div>
                                        <div class="text-xs font-bold uppercase tracking-wider text-slate-300">{{ $day['date_formatted'] }}</div>
                                        <div class="mt-2 text-2xl font-black">{{ $day['avg_night_cloud'] }}%</div>
                                        <div class="text-xs font-semibold mt-0.5">Avg Night Cloud</div>
                                    </div>
                                    <div class="mt-4 pt-3 border-t border-white/10 text-[11px] space-y-1">
                                        <div class="flex justify-between text-slate-400">
                                            <span>Sunset:</span>
                                            <span class="text-slate-200 font-medium">{{ $day['sunset'] ?? '—' }}</span>
                                        </div>
                                        <div class="flex justify-between text-slate-400">
                                            <span>Sunrise:</span>
                                            <span class="text-slate-200 font-medium">{{ $day['sunrise'] ?? '—' }}</span>
                                        </div>
                                        <div class="mt-2 text-center">
                                            <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider {{ $day['avg_night_cloud'] <= 25 ? 'bg-emerald-500/20 text-emerald-300' : ($day['avg_night_cloud'] <= 60 ? 'bg-yellow-500/20 text-yellow-300' : 'bg-slate-700/50 text-slate-400') }}">
                                                {{ $day['status'] }} Sky
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-slate-400 italic">Live forecast data is momentarily syncing. Check back in a few moments.</p>
                    @endif
                </div>
            </div>

            <!-- Sidebar / Right Column -->
            <div class="space-y-6">
                <!-- Sky Quality Rating Card -->
                <div class="p-6 rounded-3xl bg-slate-900/80 border border-slate-800 shadow-xl space-y-4">
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <span>✨</span> Darkness &amp; Sky Quality
                    </h3>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        {{ $spot->bortle_description }}.
                    </p>
                    <div class="space-y-2 text-xs">
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-950/70 border border-slate-800">
                            <span class="text-slate-400">Naked-Eye Limiting Mag:</span>
                            <span class="font-bold text-purple-300">
                                {{ $spot->bortle_class === 1 ? '7.6 – 8.0 mag' : ($spot->bortle_class === 2 ? '7.1 – 7.5 mag' : '6.6 – 7.0 mag') }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-950/70 border border-slate-800">
                            <span class="text-slate-400">Milky Way Visibility:</span>
                            <span class="font-bold text-emerald-400">
                                {{ $spot->bortle_class <= 2 ? 'Brilliant with complex structures' : 'Clearly visible with dark lanes' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-950/70 border border-slate-800">
                            <span class="text-slate-400">Zodiacal Light:</span>
                            <span class="font-bold text-cyan-300">
                                {{ $spot->bortle_class === 1 ? 'Striking & yellowish' : 'Distinct in spring/autumn' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- ISS Transit Pass Alert Box -->
                <div class="p-6 rounded-3xl bg-gradient-to-br from-purple-950/60 to-slate-900 border border-purple-500/40 shadow-xl space-y-4">
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <span>🛰️</span> ISS Transits &amp; Flyovers
                    </h3>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Astronotify continuously computes SGP4 high-precision orbital chords for the International Space Station passing across the Sun and Moon.
                    </p>

                    @if(!empty($upcomingPasses))
                        <div class="space-y-2">
                            @foreach($upcomingPasses as $pass)
                                <div class="p-3 rounded-2xl bg-slate-950/80 border border-purple-500/30 text-xs">
                                    <div class="flex items-center justify-between font-bold text-white">
                                        <span>{{ $pass['type'] === 'sun' ? '☀️ Solar' : '🌙 Lunar' }} Transit</span>
                                        <span class="text-purple-400">{{ $pass['time_human'] }}</span>
                                    </div>
                                    <div class="text-[11px] text-slate-400 mt-1">
                                        Sep: {{ $pass['separation_degrees'] }}° &bull; Alt: {{ $pass['altitude_degrees'] }}°
                                    </div>
                                    @if(!empty($pass['token']))
                                        <a href="{{ route('transit.show', $pass['token']) }}" class="mt-2 inline-block text-[11px] text-pink-400 hover:text-pink-300 font-semibold underline">
                                            View Transit Chord Diagram &rarr;
                                        </a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-4 rounded-2xl bg-slate-950/60 border border-slate-800 text-center">
                            <p class="text-xs text-slate-400">Track this spot to compute personalized solar &amp; lunar transit predictions.</p>
                            <button 
                                type="button" 
                                wire:click="trackSpotInAccount"
                                class="mt-3 w-full py-2 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs transition"
                            >
                                Track Now (Free)
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Nearby Dark Sky Spots -->
                @if($nearbySpots->isNotEmpty())
                    <div class="p-6 rounded-3xl bg-slate-900/60 border border-slate-800 shadow-xl space-y-3">
                        <h3 class="text-sm font-bold text-white uppercase tracking-wider text-slate-400">
                            More Dark Skies in {{ $spot->country }}
                        </h3>
                        <div class="space-y-2">
                            @foreach($nearbySpots as $near)
                                <a href="{{ route('spots.show', $near->slug) }}" class="p-3 rounded-xl bg-slate-950/60 hover:bg-slate-950 border border-slate-800/80 flex items-center justify-between text-xs transition group">
                                    <div>
                                        <div class="font-bold text-white group-hover:text-purple-300 transition">{{ $near->name }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $near->region }}</div>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold border {{ $near->bortle_color }}">
                                        B{{ $near->bortle_class }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @include('components.footer')
</div>
