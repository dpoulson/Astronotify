<x-slot name="title">Top 100 Dark Sky Parks &amp; Stargazing Spots Worldwide — Astronotify</x-slot>

@push('meta')
    <meta name="description" content="Discover the world's 100 best stargazing spots, IDA dark sky parks, and astronomical reserves across the UK, Europe, United States, and Canada. Real-time weather, interactive global map, and ISS transit tracking.">
    <meta property="og:title" content="Top 100 Dark Sky Parks &amp; Stargazing Spots — Astronotify">
    <meta property="og:description" content="Explore Bortle 1 &amp; 2 dark sky reserves across the UK, Europe, USA, and Canada with interactive global map, astronomy forecasts and ISS transit predictions.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('spots.index') }}">
    <meta property="og:image" content="{{ asset('images/og-card.png') }}">
    <link rel="canonical" href="{{ route('spots.index') }}">
    
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "ItemList",
        "name": "Top 100 Stargazing Spots & Dark Sky Reserves",
        "description": "Curated directory of premier dark-sky parks and astronomical observation locations worldwide.",
        "url": "{{ route('spots.index') }}",
        "numberOfItems": {{ $spots->total() }}
    }
    </script>
@endpush

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <style>
        .leaflet-container {
            background-color: #020617 !important;
            font-family: inherit !important;
        }
        .leaflet-tile-pane {
            filter: invert(1) hue-rotate(210deg) saturate(0.6) brightness(0.8) contrast(1.2);
        }
        .leaflet-popup-content-wrapper {
            background: rgba(15, 23, 42, 0.95) !important;
            color: #f8fafc !important;
            border: 1px solid rgba(148, 163, 184, 0.25) !important;
            border-radius: 1.25rem !important;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.6), 0 8px 10px -6px rgba(0, 0, 0, 0.5) !important;
            backdrop-filter: blur(12px) !important;
            padding: 4px !important;
        }
        .leaflet-popup-content {
            margin: 12px 14px !important;
            line-height: 1.4 !important;
        }
        .leaflet-popup-tip {
            background: rgba(15, 23, 42, 0.95) !important;
            border: 1px solid rgba(148, 163, 184, 0.25) !important;
        }
        .leaflet-popup-close-button {
            color: #94a3b8 !important;
            padding: 8px 8px 0 0 !important;
        }
        .leaflet-popup-close-button:hover {
            color: #ffffff !important;
        }
        .leaflet-bar a {
            background-color: #0f172a !important;
            color: #cbd5e1 !important;
            border-color: #334155 !important;
        }
        .leaflet-bar a:hover {
            background-color: #1e293b !important;
            color: #ffffff !important;
        }
        .leaflet-control-attribution {
            background: rgba(2, 6, 23, 0.85) !important;
            color: #64748b !important;
            font-size: 10px !important;
            border-radius: 6px !important;
            margin: 4px !important;
            padding: 2px 6px !important;
        }
        .leaflet-control-attribution a {
            color: #94a3b8 !important;
        }
        @keyframes custom-radar-ping {
            75%, 100% {
                transform: scale(2.2);
                opacity: 0;
            }
        }
        .animate-radar {
            animation: custom-radar-ping 2s cubic-bezier(0, 0, 0.2, 1) infinite;
        }
    </style>
@endpush

<div 
    x-data="astronotifySpotsMap({ spots: {{ Js::from($mapSpots) }}, view: @entangle('view') })" 
    class="min-h-screen bg-slate-950 text-white selection:bg-purple-500 selection:text-white"
>
    <!-- Hero Section -->
    <div class="relative overflow-hidden pt-12 pb-16 border-b border-slate-850">
        <div class="absolute inset-0 pointer-events-none opacity-40">
            <div class="absolute top-[-10%] left-[-5%] w-[45%] h-[45%] bg-purple-900/30 rounded-full blur-[140px]"></div>
            <div class="absolute top-[20%] right-[-5%] w-[50%] h-[50%] bg-blue-900/25 rounded-full blur-[160px]"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <!-- Navigation Breadcrumbs -->
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-6 font-medium">
                <a href="{{ url('/') }}" class="hover:text-purple-400 transition">Home</a>
                <span>/</span>
                <span class="text-slate-200">Stargazing Spots</span>
            </div>

            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
                <div class="max-w-3xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-purple-950/70 border border-purple-500/40 text-purple-300 text-xs font-bold uppercase tracking-wider mb-4 shadow-sm">
                        <span class="w-2 h-2 rounded-full bg-purple-400 animate-pulse"></span>
                        Curated Global Directory
                    </div>
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-white via-slate-100 to-purple-200">
                        Top Stargazing Spots &amp; Dark Sky Parks
                    </h1>
                    <p class="mt-4 text-slate-300 text-base sm:text-lg max-w-2xl leading-relaxed">
                        Explore <strong class="text-white">100 premier observing locations</strong> across the United Kingdom, Europe, North America, and Canada. Browse the directory or switch to the interactive global map to discover dark-sky reserves near you.
                    </p>
                </div>

                {{-- Metric Badges --}}
                <div class="flex flex-wrap items-center gap-3">
                    <div class="px-4 py-3 rounded-2xl bg-slate-900/80 border border-slate-800 text-center">
                        <span class="block text-2xl font-black text-white">{{ $totalSpots }}</span>
                        <span class="text-[11px] text-slate-400 uppercase tracking-wider font-semibold">Total Spots</span>
                    </div>
                    <div class="px-4 py-3 rounded-2xl bg-emerald-950/40 border border-emerald-500/30 text-center">
                        <span class="block text-2xl font-black text-emerald-400">{{ $bortle1Count }}</span>
                        <span class="text-[11px] text-emerald-300 uppercase tracking-wider font-semibold">Bortle 1 True Dark</span>
                    </div>
                    <div class="px-4 py-3 rounded-2xl bg-teal-950/40 border border-teal-500/30 text-center">
                        <span class="block text-2xl font-black text-teal-400">{{ $bortle2Count }}</span>
                        <span class="text-[11px] text-teal-300 uppercase tracking-wider font-semibold">Bortle 2 Pristine</span>
                    </div>
                </div>
            </div>

            <!-- Filters Bar & View Switcher -->
            <div class="mt-10 p-4 rounded-2xl bg-slate-900/90 border border-slate-800 shadow-xl flex flex-col xl:flex-row items-stretch xl:items-center justify-between gap-4 backdrop-blur-md">
                <div class="flex-1 max-w-md relative">
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Search by spot name, region, or park..." 
                        class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-purple-500 transition shadow-inner"
                    />
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <select 
                        wire:model.live="country" 
                        class="bg-slate-950 border border-slate-700/80 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-purple-500 transition cursor-pointer font-medium"
                    >
                        <option value="">All Countries (Worldwide)</option>
                        @foreach($countries as $c)
                            <option value="{{ $c }}">{{ $c }}</option>
                        @endforeach
                    </select>

                    <select 
                        wire:model.live="bortle" 
                        class="bg-slate-950 border border-slate-700/80 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-purple-500 transition cursor-pointer font-medium"
                    >
                        <option value="">All Light Levels</option>
                        <option value="1">Bortle 1 Only (Exceptional)</option>
                        <option value="2">Bortle ≤ 2 (Truly Dark)</option>
                        <option value="3">Bortle ≤ 3 (Rural Sky)</option>
                    </select>

                    @if(!empty($search) || !empty($country) || !empty($bortle))
                        <button 
                            type="button" 
                            wire:click="resetFilters" 
                            class="text-xs text-slate-400 hover:text-white px-3 py-2 rounded-xl bg-slate-800/80 hover:bg-slate-800 transition"
                        >
                            Reset
                        </button>
                    @endif

                    <div class="h-6 w-px bg-slate-800 hidden sm:block"></div>

                    <!-- Geolocation Near Me Button -->
                    <button 
                        type="button" 
                        @click="locateUser()" 
                        :disabled="locating"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-purple-950/70 hover:bg-purple-900/80 border border-purple-500/40 text-xs font-bold text-purple-200 hover:text-white transition shadow-sm active:scale-95 disabled:opacity-50"
                        title="Find stargazing spots closest to your current position"
                    >
                        <template x-if="!locating">
                            <span class="text-sm">📍</span>
                        </template>
                        <template x-if="locating">
                            <svg class="animate-spin h-3.5 w-3.5 text-purple-300" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                        </template>
                        <span x-text="locating ? 'Locating...' : 'Find Near Me'">Find Near Me</span>
                    </button>

                    <!-- View Switcher (Grid vs Global Map) -->
                    <div class="inline-flex rounded-xl bg-slate-950 p-1 border border-slate-800 shadow-inner">
                        <button 
                            type="button" 
                            @click="view = 'grid'; $wire.setView('grid')" 
                            :class="view === 'grid' ? 'bg-purple-600 text-white shadow-md' : 'text-slate-400 hover:text-white'"
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition"
                            title="Card Grid View"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <rect x="3" y="3" width="7" height="7" rx="1.5" stroke-width="2"/>
                                <rect x="14" y="3" width="7" height="7" rx="1.5" stroke-width="2"/>
                                <rect x="14" y="14" width="7" height="7" rx="1.5" stroke-width="2"/>
                                <rect x="3" y="14" width="7" height="7" rx="1.5" stroke-width="2"/>
                            </svg>
                            <span>Grid</span>
                        </button>
                        <button 
                            type="button" 
                            @click="view = 'map'; $wire.setView('map'); $nextTick(() => ensureMapInitialized())" 
                            :class="view === 'map' ? 'bg-purple-600 text-white shadow-md' : 'text-slate-400 hover:text-white'"
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition"
                            title="Interactive Global Map View"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="9" stroke-width="2"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.6 9h16.8M3.6 15h16.8M12 3a15.3 15.3 0 0 1 4 9 15.3 15.3 0 0 1-4 9 15.3 15.3 0 0 1-4-9 15.3 15.3 0 0 1 4-9z"/>
                            </svg>
                            <span>Global Map</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Geolocation Status / Error notification -->
            <div x-show="locationError" x-cloak class="mt-4 p-3 rounded-xl bg-rose-950/70 border border-rose-500/40 text-xs text-rose-200 flex items-center justify-between">
                <span x-text="locationError"></span>
                <button type="button" @click="locationError = null" class="text-rose-400 hover:text-white ml-2 text-sm">&times;</button>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <!-- INTERACTIVE GLOBAL MAP VIEW -->
        <div x-show="view === 'map'" x-cloak class="space-y-6">
            <div class="relative rounded-3xl bg-slate-900 border border-slate-800 shadow-2xl overflow-hidden">
                <!-- Map Top Overlay Header -->
                <div class="absolute top-4 left-4 right-4 z-[400] flex flex-wrap items-center justify-between gap-3 pointer-events-none">
                    <div class="flex items-center gap-2 pointer-events-auto">
                        <div class="px-3.5 py-2 rounded-xl bg-slate-950/90 border border-slate-700/80 backdrop-blur-md text-xs font-bold text-slate-200 shadow-xl flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>Showing <strong class="text-white" x-text="spots.length">0</strong> Spots</span>
                        </div>

                        <template x-if="userLat && userLng">
                            <div class="px-3 py-2 rounded-xl bg-cyan-950/90 border border-cyan-500/40 backdrop-blur-md text-xs font-bold text-cyan-300 shadow-xl flex items-center gap-1.5">
                                <span>📍</span>
                                <span>GPS Active</span>
                            </div>
                        </template>
                    </div>

                    <div class="flex items-center gap-2 pointer-events-auto">
                        <button 
                            type="button" 
                            @click="fitAllSpots()" 
                            class="px-3 py-2 rounded-xl bg-slate-950/90 hover:bg-slate-900 border border-slate-700/80 text-xs font-bold text-slate-200 hover:text-white backdrop-blur-md transition shadow-xl flex items-center gap-1.5"
                            title="Reset map to fit all spots worldwide"
                        >
                            <span>🌍</span>
                            <span>World View</span>
                        </button>

                        <button 
                            type="button" 
                            @click="locateUser()" 
                            :disabled="locating"
                            class="px-3 py-2 rounded-xl bg-purple-950/90 hover:bg-purple-900 border border-purple-500/40 text-xs font-bold text-purple-200 hover:text-white backdrop-blur-md transition shadow-xl flex items-center gap-1.5 disabled:opacity-50"
                            title="Center map on your location"
                        >
                            <span>🎯</span>
                            <span x-text="locating ? 'Locating...' : 'My Location'">My Location</span>
                        </button>
                    </div>
                </div>

                <!-- Leaflet Container -->
                <div id="stargazing-spots-map" class="w-full h-[620px] sm:h-[720px] z-0"></div>

                <!-- Floating Nearest Spots Drawer (When user location is acquired) -->
                <div 
                    x-show="userLat && userLng && closestSpots.length > 0" 
                    x-cloak 
                    class="absolute bottom-4 left-4 z-[400] max-w-sm w-full pointer-events-auto"
                >
                    <div class="rounded-2xl bg-slate-950/95 border border-purple-500/40 backdrop-blur-md shadow-2xl p-4">
                        <div class="flex items-center justify-between border-b border-slate-800 pb-2 mb-3">
                            <div class="flex items-center gap-1.5 text-xs font-bold text-purple-300">
                                <span class="text-sm">📍</span>
                                <span>Nearest Dark Sky Spots to You</span>
                            </div>
                            <button 
                                type="button" 
                                @click="drawerOpen = !drawerOpen" 
                                class="text-xs text-slate-400 hover:text-white px-2 py-0.5 rounded bg-slate-900"
                                x-text="drawerOpen ? 'Hide' : 'Show'"
                            ></button>
                        </div>

                        <div x-show="drawerOpen" class="space-y-2.5 max-h-56 overflow-y-auto pr-1">
                            <template x-for="(spot, idx) in closestSpots" :key="spot.id">
                                <div class="p-2.5 rounded-xl bg-slate-900/80 border border-slate-800 hover:border-purple-500/50 flex items-center justify-between gap-3 transition">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] font-black px-1.5 py-0.5 rounded bg-purple-950 border border-purple-500/40 text-purple-300" x-text="'#' + (idx + 1)"></span>
                                            <h5 class="text-xs font-bold text-white truncate hover:text-purple-300 cursor-pointer" @click="flyToSpot(spot)" x-text="spot.name"></h5>
                                        </div>
                                        <div class="text-[11px] text-slate-400 mt-1 flex items-center gap-2">
                                            <span class="text-emerald-400 font-semibold" x-text="spot.distanceMiles + ' mi away'"></span>
                                            <span>•</span>
                                            <span x-text="'Bortle ' + spot.bortle_class"></span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1.5 shrink-0">
                                        <button 
                                            type="button" 
                                            @click="flyToSpot(spot)" 
                                            class="px-2 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-[11px] font-bold transition"
                                            title="Fly to marker on map"
                                        >
                                            Fly ↗
                                        </button>
                                        <a 
                                            :href="spot.url" 
                                            class="px-2 py-1 rounded-lg bg-purple-600 hover:bg-purple-500 text-white text-[11px] font-bold transition"
                                            title="View Weather & ISS Transits"
                                        >
                                            View
                                        </a>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Prompt to locate if not yet located -->
                <div 
                    x-show="!userLat && !userLng" 
                    x-cloak 
                    class="absolute bottom-4 left-4 z-[400] max-w-sm pointer-events-auto"
                >
                    <div class="rounded-2xl bg-slate-950/90 border border-slate-800 backdrop-blur-md shadow-xl p-3 flex items-center gap-3">
                        <span class="text-lg">🔭</span>
                        <div class="text-xs">
                            <p class="text-white font-semibold">Find spots closest to you</p>
                            <p class="text-slate-400 text-[11px]">Calculate distances from your current location</p>
                        </div>
                        <button 
                            type="button" 
                            @click="locateUser()" 
                            :disabled="locating" 
                            class="px-3 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white text-xs font-bold shrink-0 transition"
                        >
                            Locate
                        </button>
                    </div>
                </div>

                <!-- Bortle Scale Legend -->
                <div class="absolute bottom-4 right-4 z-[400] pointer-events-auto hidden md:block">
                    <div class="px-3.5 py-2.5 rounded-2xl bg-slate-950/90 border border-slate-800 backdrop-blur-md shadow-xl text-xs">
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Bortle Scale</span>
                        <div class="flex items-center gap-3 text-[11px]">
                            <span class="flex items-center gap-1.5 text-emerald-400 font-semibold"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> B1 True Dark</span>
                            <span class="flex items-center gap-1.5 text-teal-400 font-semibold"><span class="w-2.5 h-2.5 rounded-full bg-teal-500"></span> B2 Pristine</span>
                            <span class="flex items-center gap-1.5 text-cyan-400 font-semibold"><span class="w-2.5 h-2.5 rounded-full bg-cyan-500"></span> B3 Rural</span>
                            <span class="flex items-center gap-1.5 text-blue-400 font-semibold"><span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span> B4+ Transition</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- GRID DIRECTORY VIEW -->
        <div x-show="view === 'grid'">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($spots as $spot)
                    <div class="group relative rounded-3xl bg-slate-900/70 border border-slate-800 hover:border-purple-500/50 p-6 flex flex-col justify-between transition-all duration-300 hover:shadow-2xl hover:shadow-purple-900/10 hover:-translate-y-1">
                        <div>
                            <!-- Top Metadata Row -->
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <span class="text-xs font-semibold text-slate-400 flex items-center gap-1.5">
                                    <span class="text-sm">📍</span>
                                    <span>{{ $spot->region ? $spot->region . ', ' : '' }}{{ $spot->country }}</span>
                                </span>
                                <span class="px-2.5 py-1 rounded-xl text-xs font-bold border {{ $spot->bortle_color }}">
                                    Bortle {{ $spot->bortle_class }}
                                </span>
                            </div>

                            <!-- Spot Title -->
                            <h2 class="text-xl font-bold text-white group-hover:text-purple-300 transition-colors">
                                <a href="{{ route('spots.show', $spot->slug) }}">
                                    {{ $spot->name }}
                                </a>
                            </h2>

                            <!-- Dark Sky Status Badge -->
                            @if($spot->dark_sky_status)
                                <div class="mt-2.5">
                                    <span class="inline-block px-2.5 py-1 rounded-lg bg-indigo-950/70 border border-indigo-500/30 text-indigo-300 text-[11px] font-semibold tracking-wide">
                                        ★ {{ $spot->dark_sky_status }}
                                    </span>
                                </div>
                            @endif

                            <!-- Proximity Distance Tag if User is Located -->
                            <div 
                                x-show="userLat && userLng" 
                                x-cloak 
                                class="mt-2 text-xs font-semibold text-purple-300 flex items-center gap-1"
                            >
                                <span x-text="getDistanceText({{ $spot->latitude }}, {{ $spot->longitude }})"></span>
                            </div>

                            <!-- Description Excerpt -->
                            <p class="mt-3 text-slate-400 text-xs sm:text-sm line-clamp-3 leading-relaxed">
                                {{ $spot->description }}
                            </p>
                        </div>

                        <div class="mt-6 pt-4 border-t border-slate-800/80 flex items-center justify-between text-xs">
                            <div class="text-slate-400 font-mono text-[11px]">
                                {{ round($spot->latitude, 3) }}°, {{ round($spot->longitude, 3) }}°
                                <span class="text-slate-400 block">{{ $spot->elevation }}m altitude</span>
                            </div>

                            <div class="flex items-center gap-2">
                                <button 
                                    type="button" 
                                    @click="flyToSpot({ id: {{ $spot->id }}, lat: {{ $spot->latitude }}, lng: {{ $spot->longitude }}, name: '{{ addslashes($spot->name) }}', slug: '{{ $spot->slug }}' })" 
                                    class="inline-flex items-center gap-1 px-2.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-semibold transition border border-slate-700/80"
                                    title="View this spot on global map"
                                >
                                    <span>🗺️</span>
                                    <span>Map</span>
                                </button>

                                <a 
                                    href="{{ route('spots.show', $spot->slug) }}" 
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-purple-950/80 hover:bg-purple-900 border border-purple-500/40 text-purple-200 text-xs font-bold transition group-hover:bg-purple-800 group-hover:text-white"
                                >
                                    <span>Forecast &amp; ISS</span>
                                    <span class="transition-transform group-hover:translate-x-1">&rarr;</span>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-16 text-center rounded-3xl bg-slate-900/40 border border-slate-800">
                        <p class="text-slate-400 text-base">No stargazing spots found matching your filter criteria.</p>
                        <button 
                            type="button" 
                            wire:click="resetFilters" 
                            class="mt-4 px-4 py-2 rounded-xl text-xs font-bold bg-purple-600 hover:bg-purple-500 text-white transition"
                        >
                            Clear All Filters
                        </button>
                    </div>
                @endforelse
            </div>

            <div class="mt-10">
                {{ $spots->links() }}
            </div>
        </div>

        <!-- SEO Callout & Community CTA -->
        <div class="mt-16 p-8 rounded-3xl bg-gradient-to-r from-purple-950/50 via-slate-900 to-blue-950/50 border border-purple-500/30 shadow-2xl flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="max-w-2xl">
                <h3 class="text-2xl font-bold text-white">Know a dark-sky spot that should be listed?</h3>
                <p class="text-sm text-slate-300 mt-2">
                    Astronotify maintains a global directory of accessible stargazing locations, public observatories, and designated dark-sky parks. You can also save any custom coordinates to your personal account for automated alerts.
                </p>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ route('register') }}" class="px-5 py-3 rounded-2xl bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-500 hover:to-blue-500 text-white font-bold text-sm shadow-xl transition active:scale-95">
                    Create Free Account
                </a>
            </div>
        </div>
    </div>

    @include('components.footer')
</div>

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        function astronotifySpotsMap(config) {
            return {
                view: config.view || 'grid',
                spots: config.spots || [],
                userLat: null,
                userLng: null,
                locating: false,
                locationError: null,
                closestSpots: [],
                map: null,
                markersLayer: null,
                userMarker: null,
                drawerOpen: true,

                init() {
                    Livewire.on('map-spots-updated', (data) => {
                        this.spots = Array.isArray(data) ? data : (data.spots || []);
                        if (this.map) {
                            this.renderMarkers(this.spots);
                        }
                        if (this.userLat && this.userLng) {
                            this.calculateDistances();
                        }
                    });

                    this.$watch('view', (newView) => {
                        if (newView === 'map') {
                            this.$nextTick(() => {
                                this.ensureMapInitialized();
                            });
                        }
                    });

                    if (this.view === 'map') {
                        this.$nextTick(() => {
                            this.ensureMapInitialized();
                        });
                    }
                },

                ensureMapInitialized() {
                    if (!window.L) {
                        setTimeout(() => this.ensureMapInitialized(), 80);
                        return;
                    }

                    const mapEl = document.getElementById('stargazing-spots-map');
                    if (!mapEl) return;

                    if (!this.map) {
                        this.map = L.map('stargazing-spots-map', {
                            zoomControl: false,
                            minZoom: 2,
                            maxZoom: 18,
                            worldCopyJump: true,
                        }).setView([35.0, -20.0], 3);

                        L.control.zoom({ position: 'topright' }).addTo(this.map);

                        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a> contributors',
                            maxZoom: 19
                        }).addTo(this.map);

                        this.markersLayer = L.layerGroup().addTo(this.map);
                        this.renderMarkers(this.spots);
                    }

                    setTimeout(() => {
                        if (this.map) {
                            this.map.invalidateSize();
                        }
                    }, 120);
                },

                renderMarkers(spotsList) {
                    if (!this.map || !this.markersLayer) return;

                    this.markersLayer.clearLayers();
                    const bounds = [];

                    spotsList.forEach((spot) => {
                        if (spot.lat === null || spot.lng === null || isNaN(spot.lat) || isNaN(spot.lng)) return;

                        const latLng = [spot.lat, spot.lng];
                        bounds.push(latLng);

                        let bg = '#10b981';
                        let border = '#34d399';
                        let shadow = 'rgba(16, 185, 129, 0.4)';
                        if (spot.bortle_class === 2) {
                            bg = '#0d9488';
                            border = '#2dd4bf';
                            shadow = 'rgba(13, 148, 136, 0.4)';
                        } else if (spot.bortle_class === 3) {
                            bg = '#0284c7';
                            border = '#38bdf8';
                            shadow = 'rgba(2, 132, 199, 0.4)';
                        } else if (spot.bortle_class === 4) {
                            bg = '#3b82f6';
                            border = '#60a5fa';
                            shadow = 'rgba(59, 130, 246, 0.4)';
                        } else if (spot.bortle_class >= 5) {
                            bg = '#f59e0b';
                            border = '#fbbf24';
                            shadow = 'rgba(245, 158, 11, 0.4)';
                        }

                        const icon = L.divIcon({
                            className: 'custom-spot-pin',
                            html: `
                                <div class="flex flex-col items-center cursor-pointer transition-transform duration-200 hover:scale-125" style="transform-origin: bottom center;">
                                    <div class="px-2 py-0.5 rounded-full text-[10px] font-black tracking-tight text-white flex items-center justify-center shadow-lg border" style="background-color: ${bg}; border-color: ${border}; box-shadow: 0 0 10px ${shadow};">
                                        <span>B${spot.bortle_class}</span>
                                    </div>
                                    <div class="w-1.5 h-1.5 rounded-full mt-0.5" style="background-color: ${border};"></div>
                                </div>
                            `,
                            iconSize: [36, 30],
                            iconAnchor: [18, 28],
                            popupAnchor: [0, -28],
                        });

                        const marker = L.marker(latLng, { icon: icon });
                        marker.bindPopup(this.buildPopupHtml(spot), {
                            maxWidth: 320,
                            className: 'dark-astronomy-popup'
                        });

                        this.markersLayer.addLayer(marker);
                    });

                    if (bounds.length > 0 && !this.userLat) {
                        this.map.fitBounds(bounds, { padding: [40, 40], maxZoom: 6 });
                    }
                },

                buildPopupHtml(spot) {
                    let distHtml = '';
                    if (this.userLat && this.userLng) {
                        const dist = this.haversine(this.userLat, this.userLng, spot.lat, spot.lng);
                        distHtml = `
                            <div class="text-[11px] font-bold text-purple-300 my-2 flex items-center gap-1.5 bg-purple-950/70 border border-purple-500/30 px-2.5 py-1 rounded-lg">
                                <span>🧭</span>
                                <span><strong>${dist.miles} miles</strong> (${dist.km} km) from you</span>
                            </div>
                        `;
                    }

                    const darkSkyBadge = spot.dark_sky_status
                        ? `<div class="mb-2"><span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-950/80 border border-indigo-500/40 text-indigo-300">★ ${spot.dark_sky_status}</span></div>`
                        : '';

                    return `
                        <div class="p-1 text-slate-100 font-sans" style="min-width: 250px; max-width: 300px;">
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400">📍 ${spot.region ? spot.region + ', ' : ''}${spot.country}</span>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold border ${spot.bortle_color}">Bortle ${spot.bortle_class}</span>
                            </div>
                            <h4 class="font-bold text-white text-base leading-tight mb-1 hover:text-purple-300">
                                <a href="${spot.url}">${spot.name}</a>
                            </h4>
                            ${darkSkyBadge}
                            <p class="text-xs text-slate-300 leading-relaxed mb-2 line-clamp-3">${spot.description || ''}</p>
                            ${distHtml}
                            <div class="flex items-center justify-between pt-2.5 border-t border-slate-800 text-xs">
                                <span class="text-slate-400 font-mono text-[11px]">${spot.elevation}m altitude</span>
                                <a href="${spot.url}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs transition shadow-md">
                                    <span>Forecast & ISS</span>
                                    <span>&rarr;</span>
                                </a>
                            </div>
                        </div>
                    `;
                },

                haversine(lat1, lon1, lat2, lon2) {
                    const R = 6371; // km
                    const dLat = (lat2 - lat1) * Math.PI / 180;
                    const dLon = (lon2 - lon1) * Math.PI / 180;
                    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                              Math.sin(dLon / 2) * Math.sin(dLon / 2);
                    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                    const km = R * c;
                    return {
                        km: Math.round(km),
                        miles: Math.round(km * 0.621371)
                    };
                },

                getDistanceText(lat, lng) {
                    if (!this.userLat || !this.userLng) return '';
                    const dist = this.haversine(this.userLat, this.userLng, lat, lng);
                    return `🧭 ${dist.miles} mi (${dist.km} km) away`;
                },

                locateUser() {
                    if (!navigator.geolocation) {
                        this.locationError = 'Geolocation is not supported by your browser.';
                        return;
                    }

                    this.locating = true;
                    this.locationError = null;

                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            this.locating = false;
                            this.userLat = position.coords.latitude;
                            this.userLng = position.coords.longitude;

                            if (this.view !== 'map') {
                                this.view = 'map';
                                this.$wire.setView('map');
                            }

                            this.calculateDistances();

                            this.$nextTick(() => {
                                this.ensureMapInitialized();
                                this.showUserOnMap();
                            });
                        },
                        (error) => {
                            this.locating = false;
                            let msg = 'Could not retrieve your location.';
                            if (error.code === 1) msg = 'Location access was denied. Please allow location access to find nearby spots.';
                            else if (error.code === 2) msg = 'Location unavailable. Ensure GPS is active.';
                            else if (error.code === 3) msg = 'Location request timed out. Please try again.';
                            this.locationError = msg;
                        },
                        { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 }
                    );
                },

                calculateDistances() {
                    if (!this.userLat || !this.userLng) return;

                    const list = this.spots.map((spot) => {
                        const dist = this.haversine(this.userLat, this.userLng, spot.lat, spot.lng);
                        return {
                            ...spot,
                            distanceKm: dist.km,
                            distanceMiles: dist.miles
                        };
                    });

                    list.sort((a, b) => (a.distanceKm || 999999) - (b.distanceKm || 999999));
                    this.closestSpots = list.slice(0, 5);
                },

                showUserOnMap() {
                    if (!this.map || !this.userLat || !this.userLng) return;

                    if (this.userMarker) {
                        this.map.removeLayer(this.userMarker);
                    }

                    const userIcon = L.divIcon({
                        className: 'user-location-pulse-marker',
                        html: `
                            <div class="relative flex items-center justify-center w-8 h-8">
                                <span class="animate-radar absolute inline-flex h-8 w-8 rounded-full bg-cyan-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-4 w-4 bg-cyan-500 border-2 border-white shadow-[0_0_14px_rgba(6,182,212,0.9)]"></span>
                            </div>
                        `,
                        iconSize: [32, 32],
                        iconAnchor: [16, 16],
                    });

                    this.userMarker = L.marker([this.userLat, this.userLng], {
                        icon: userIcon,
                        zIndexOffset: 1000
                    }).addTo(this.map);

                    this.userMarker.bindPopup(`
                        <div class="p-1 font-sans text-center text-white">
                            <span class="text-xs font-bold text-cyan-400">📍 You Are Here</span>
                            <p class="text-[11px] text-slate-300 mt-0.5">Showing dark sky parks near your coordinates</p>
                        </div>
                    `);

                    this.renderMarkers(this.spots);

                    if (this.closestSpots.length > 0) {
                        const bounds = [
                            [this.userLat, this.userLng],
                            [this.closestSpots[0].lat, this.closestSpots[0].lng]
                        ];
                        if (this.closestSpots[1]) {
                            bounds.push([this.closestSpots[1].lat, this.closestSpots[1].lng]);
                        }
                        this.map.fitBounds(bounds, { padding: [80, 80], maxZoom: 8 });
                    } else {
                        this.map.setView([this.userLat, this.userLng], 7);
                    }
                },

                flyToSpot(spot) {
                    if (this.view !== 'map') {
                        this.view = 'map';
                        this.$wire.setView('map');
                    }
                    this.$nextTick(() => {
                        this.ensureMapInitialized();
                        if (this.map && spot.lat && spot.lng) {
                            this.map.flyTo([spot.lat, spot.lng], 9, { duration: 1.2 });
                            if (this.markersLayer) {
                                this.markersLayer.eachLayer((layer) => {
                                    const latLng = layer.getLatLng();
                                    if (Math.abs(latLng.lat - spot.lat) < 0.0001 && Math.abs(latLng.lng - spot.lng) < 0.0001) {
                                        layer.openPopup();
                                    }
                                });
                            }
                        }
                    });
                },

                fitAllSpots() {
                    if (!this.map || !this.markersLayer) return;
                    const bounds = [];
                    this.markersLayer.eachLayer((layer) => {
                        bounds.push(layer.getLatLng());
                    });
                    if (bounds.length > 0) {
                        this.map.fitBounds(bounds, { padding: [50, 50], maxZoom: 6 });
                    }
                }
            };
        }
    </script>
@endpush
