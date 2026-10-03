<x-slot name="title">Top 100 Dark Sky Parks &amp; Stargazing Spots Worldwide — Astronotify</x-slot>

@push('meta')
    <meta name="description" content="Discover the world's 100 best stargazing spots, IDA dark sky parks, and astronomical reserves across the UK, Europe, United States, and Canada. Real-time weather and ISS transit tracking.">
    <meta property="og:title" content="Top 100 Dark Sky Parks &amp; Stargazing Spots — Astronotify">
    <meta property="og:description" content="Explore Bortle 1 &amp; 2 dark sky reserves across the UK, Europe, USA, and Canada with real-time astronomy forecasts and ISS transit predictions.">
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

<div class="min-h-screen bg-slate-950 text-white selection:bg-purple-500 selection:text-white">
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
                        Explore <strong class="text-white">100 premier observing locations</strong> across the United Kingdom, Europe, North America, and Canada. Check live cloud forecasts, Bortle classes, and upcoming International Space Station solar/lunar transits.
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

            <!-- Filters Bar -->
            <div class="mt-10 p-4 rounded-2xl bg-slate-900/90 border border-slate-800 shadow-xl flex flex-col md:flex-row items-center justify-between gap-4 backdrop-blur-md">
                <div class="w-full md:w-96 relative">
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Search by spot name, region, or park..." 
                        class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-purple-500 transition shadow-inner"
                    />
                </div>

                <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
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
                            wire:click="$set('search', ''); $set('country', ''); $set('bortle', '')" 
                            class="text-xs text-slate-400 hover:text-white px-3 py-2 rounded-xl bg-slate-800/80 hover:bg-slate-800 transition"
                        >
                            Reset
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Main Directory Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
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

                        <a 
                            href="{{ route('spots.show', $spot->slug) }}" 
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-purple-950/80 hover:bg-purple-900 border border-purple-500/40 text-purple-200 text-xs font-bold transition group-hover:bg-purple-800 group-hover:text-white"
                        >
                            <span>Forecast &amp; ISS</span>
                            <span class="transition-transform group-hover:translate-x-1">&rarr;</span>
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 text-center rounded-3xl bg-slate-900/40 border border-slate-800">
                    <p class="text-slate-400 text-base">No stargazing spots found matching your filter criteria.</p>
                    <button 
                        type="button" 
                        wire:click="$set('search', ''); $set('country', ''); $set('bortle', '')" 
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
