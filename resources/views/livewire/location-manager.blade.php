<div 
    x-data="astronotifyLocationManager({ formOpen: {{ ($locations->isEmpty() || $editingLocationId) ? 'true' : 'false' }} })"
    x-init="initPwa()"
    x-on:open-form.window="formOpen = true"
    x-on:close-form.window="formOpen = false"
    x-on:keydown.escape.window="transitModal = null"
    class="p-6 lg:p-8 bg-slate-900 border border-slate-800 text-white relative overflow-hidden sm:rounded-3xl shadow-2xl"
>
    <!-- Background Accents -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
        <div class="absolute top-[-20%] left-[-10%] w-[50%] h-[50%] bg-purple-900/30 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-[-20%] right-[-10%] w-[60%] h-[60%] bg-blue-900/20 rounded-full blur-[150px]"></div>
    </div>

    <div class="relative z-10 w-full space-y-12">
        <!-- PWA / Mobile Install Banner -->
        <div 
            x-show="showPwaBanner && !pwaInstalled" 
            x-transition 
            style="display: none;"
            class="p-4 rounded-2xl bg-gradient-to-r from-blue-900/60 via-purple-900/60 to-slate-900/90 border border-purple-500/40 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-xl"
        >
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-950 border border-purple-700/50 flex items-center justify-center text-xl shrink-0">
                    📱
                </div>
                <div>
                    <h4 class="text-sm font-bold text-white">Install Astronotify App</h4>
                    <p class="text-xs text-slate-300">Fast access to your observing spots, offline pass checks, and instant transit alerts on Android &amp; iOS.</p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button 
                    type="button" 
                    @click="installPwa()" 
                    class="px-4 py-2 rounded-xl text-xs font-bold bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-500 hover:to-purple-500 text-white shadow-md transition-all active:scale-95"
                >
                    Install Now
                </button>
                <button 
                    type="button" 
                    @click="dismissPwa()" 
                    class="p-2 rounded-xl text-slate-400 hover:text-white transition-colors" 
                    title="Dismiss"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        <!-- Header -->
        <div class="text-center">
            <h1 class="text-4xl font-extrabold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-500">
                Stargazing Locations
            </h1>
            <p class="mt-2 text-lg text-slate-400">Configure your viewing spots and sky conditions.</p>
            <div class="mt-4 flex flex-wrap items-center justify-center gap-3">
                <button 
                    type="button" 
                    @click="getGpsLocation()" 
                    :disabled="locating" 
                    class="inline-flex items-center gap-2 py-2 px-4 bg-purple-950/60 hover:bg-purple-900/70 border border-purple-500/40 rounded-full text-xs font-semibold text-purple-200 shadow-sm transition-all active:scale-95"
                >
                    <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span x-text="locating ? locatingMsg : '📍 Quick Add Current GPS Location'">📍 Quick Add Current GPS Location</span>
                </button>
            </div>
            <p x-show="gpsError" x-text="gpsError" class="text-xs text-amber-400 mt-2" x-cloak></p>
        </div>

        @if (session()->has('message'))
            <div class="bg-green-500/20 border border-green-500/50 text-green-300 p-4 rounded-xl backdrop-blur-md">
                {{ session('message') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="bg-red-500/20 border border-red-500/50 text-red-300 p-4 rounded-xl backdrop-blur-md">
                {{ session('error') }}
            </div>
        @endif

        <!-- Upcoming Nights Panel -->
        @if($upcomingNights->count() > 0)
        <div x-data="{ open: true }" class="bg-gradient-to-r from-blue-900/40 to-purple-900/40 border border-purple-500/30 rounded-3xl p-6 shadow-2xl backdrop-blur-xl mb-8">
            <button @click="open = !open; $el.blur()" class="w-full flex justify-between items-center text-left focus:outline-none">
                <h2 class="text-xl font-bold text-white flex items-center">
                    <svg class="w-5 h-5 mr-3 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" /></svg>
                    Upcoming Optimal Nights ({{ $upcomingNights->count() }})
                </h2>
                <svg :class="open ? 'transform rotate-180' : ''" class="w-5 h-5 text-slate-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
            </button>
            <div x-show="open" x-collapse x-cloak>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mt-4">
                    @foreach($upcomingNights as $night)
                        <div class="bg-slate-900/60 p-4 rounded-2xl border border-purple-500/20 flex flex-col">
                            <span class="text-purple-300 font-bold text-lg">{{ \Carbon\Carbon::parse($night->date)->format('l, M jS') }}</span>
                            <span class="text-slate-200 mt-1 font-semibold flex items-center">
                                <svg class="w-4 h-4 mr-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                {{ $night->location->name }}
                            </span>
                            <span class="text-xs text-slate-400 mt-2">Conditions met ✨</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Upcoming ISS Transits Panel -->
        @if($upcomingTransits->count() > 0)
        <div x-data="{ open: true }" class="bg-gradient-to-r from-amber-950/40 to-slate-900/40 border border-amber-500/30 rounded-3xl p-6 shadow-2xl backdrop-blur-xl mb-8">
            <button @click="open = !open; $el.blur()" class="w-full flex justify-between items-center text-left focus:outline-none">
                <h2 class="text-xl font-bold text-white flex items-center">
                    <span class="mr-3 text-xl">🚀</span>
                    Upcoming ISS Transits ({{ $upcomingTransits->count() }})
                </h2>
                <svg :class="open ? 'transform rotate-180' : ''" class="w-5 h-5 text-slate-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
            </button>
            <div x-show="open" x-collapse x-cloak>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-4">
                    @foreach($upcomingTransits as $transit)
                        @php
                            // SVG canvas: 160×160 viewBox, disk radius = 50px
                            $svgCx = 80; $svgCy = 80; $diskR = 50;
                            $scale = $diskR / 0.27; // px per degree of arc

                            $rawPts = $transit->path_points ?? [];
                            $hasPath = count($rawPts) >= 1;

                            // Find closest-approach point (smallest |dx| = closest to disk centre horizontally)
                            $closestPt = collect($rawPts)->sortBy(fn($p) => abs($p['dx']))->first()
                                         ?? ['dx' => 0, 'dy' => -($transit->separation_degrees)];

                            $closestSvgX = round($svgCx + ($closestPt['dx'] * $scale), 1);
                            $closestSvgY = round($svgCy - ($closestPt['dy'] * $scale), 1);

                            // Derive direction vector from sampled points (or fallback to horizontal)
                            if (count($rawPts) >= 2) {
                                $first = $rawPts[0];
                                $last  = $rawPts[count($rawPts) - 1];
                                $vx = $last['dx'] - $first['dx'];
                                $vy = $last['dy'] - $first['dy'];
                            } else {
                                // Single point or no points: assume near-horizontal trajectory (typical for ISS)
                                $vx = 1.0; $vy = 0.0;
                            }
                            $vLen = sqrt($vx * $vx + $vy * $vy);
                            if ($vLen < 0.0001) { $vx = 1.0; $vy = 0.0; $vLen = 1.0; }
                            $vx /= $vLen; $vy /= $vLen; // normalise

                            // Extend line from closest approach in both directions until off-canvas
                            $extend = 120.0; // pixels — large enough to cross full SVG
                            $x1 = round($closestSvgX - $vx  * $scale * $extend, 1);
                            $y1 = round($closestSvgY + $vy  * $scale * $extend, 1); // SVG y inverted
                            $x2 = round($closestSvgX + $vx  * $scale * $extend, 1);
                            $y2 = round($closestSvgY - $vy  * $scale * $extend, 1);

                            $polylinePoints = "{$x1},{$y1} {$x2},{$y2}";
                        @endphp

                        <div class="bg-slate-900/60 rounded-2xl border {{ $transit->type === 'sun' ? 'border-amber-500/20' : 'border-blue-500/20' }} flex flex-col overflow-hidden">

                            {{-- Path diagram - full width centrepiece, clickable --}}
                            <button
                                type="button"
                                @click="transitModal = {{ $transit->id }}; $el.blur()"
                                class="relative w-full bg-slate-950/80 flex items-center justify-center py-2 cursor-zoom-in group focus:outline-none"
                                style="min-height:140px;"
                                title="Click to enlarge"
                            >
                                <svg class="w-full" viewBox="0 0 160 160" preserveAspectRatio="xMidYMid meet" style="max-height:160px;">
                                    <defs>
                                        @if($transit->type === 'sun')
                                        <radialGradient id="body-{{ $transit->id }}" cx="40%" cy="35%">
                                            <stop offset="0%"   stop-color="#fef08a"/>
                                            <stop offset="50%"  stop-color="#fbbf24"/>
                                            <stop offset="85%"  stop-color="#ea580c"/>
                                            <stop offset="100%" stop-color="#b45309" stop-opacity="0.6"/>
                                        </radialGradient>
                                        <!-- Glow filter for sun -->
                                        <filter id="glow-{{ $transit->id }}">
                                            <feGaussianBlur stdDeviation="4" result="coloredBlur"/>
                                            <feMerge><feMergeNode in="coloredBlur"/><feMergeNode in="SourceGraphic"/></feMerge>
                                        </filter>
                                        @else
                                        <radialGradient id="body-{{ $transit->id }}" cx="35%" cy="30%">
                                            <stop offset="0%"   stop-color="#e2e8f0"/>
                                            <stop offset="60%"  stop-color="#94a3b8"/>
                                            <stop offset="100%" stop-color="#475569"/>
                                        </radialGradient>
                                        @endif
                                        <clipPath id="disk-clip-{{ $transit->id }}">
                                            <circle cx="{{ $svgCx }}" cy="{{ $svgCy }}" r="{{ $diskR }}"/>
                                        </clipPath>
                                        <marker id="arrow-{{ $transit->id }}" markerWidth="6" markerHeight="6" refX="3" refY="3" orient="auto">
                                            <path d="M0,0 L6,3 L0,6 Z" fill="#a855f7"/>
                                        </marker>
                                    </defs>

                                    {{-- Outer glow ring --}}
                                    @if($transit->type === 'sun')
                                    <circle cx="{{ $svgCx }}" cy="{{ $svgCy }}" r="{{ $diskR + 10 }}" fill="none" stroke="#fbbf24" stroke-width="1.5" stroke-opacity="0.2"/>
                                    <circle cx="{{ $svgCx }}" cy="{{ $svgCy }}" r="{{ $diskR + 5 }}" fill="none" stroke="#f59e0b" stroke-width="1" stroke-opacity="0.3"/>
                                    @endif

                                    {{-- Main body disk --}}
                                    @if($transit->type === 'sun')
                                    <circle cx="{{ $svgCx }}" cy="{{ $svgCy }}" r="{{ $diskR }}" fill="url(#body-{{ $transit->id }})" filter="url(#glow-{{ $transit->id }})"/>
                                    @else
                                    <circle cx="{{ $svgCx }}" cy="{{ $svgCy }}" r="{{ $diskR }}" fill="url(#body-{{ $transit->id }})"/>
                                    {{-- Moon craters --}}
                                    <circle cx="{{ $svgCx - 18 }}" cy="{{ $svgCy - 15 }}" r="6"  fill="#64748b" fill-opacity="0.6"/>
                                    <circle cx="{{ $svgCx + 10 }}" cy="{{ $svgCy + 18 }}" r="9"  fill="#64748b" fill-opacity="0.5"/>
                                    <circle cx="{{ $svgCx - 5  }}" cy="{{ $svgCy + 8  }}" r="5"  fill="#64748b" fill-opacity="0.4"/>
                                    <circle cx="{{ $svgCx + 20 }}" cy="{{ $svgCy - 10 }}" r="4"  fill="#64748b" fill-opacity="0.5"/>
                                    <circle cx="{{ $svgCx - 20 }}" cy="{{ $svgCy + 5  }}" r="3"  fill="#64748b" fill-opacity="0.6"/>

                                    {{-- Dynamic Moon Phase shadow --}}
                                    @php
                                        $moon = $transit->getMoonPhase();
                                        $P = $moon['phase'];
                                        if ($P <= 0.5) {
                                            $shadowCx = $svgCx - (2 * $diskR * ($P / 0.5));
                                        } else {
                                            $shadowCx = ($svgCx + 2 * $diskR) - (2 * $diskR * (($P - 0.5) / 0.5));
                                        }
                                    @endphp
                                    <circle cx="{{ $shadowCx }}" cy="{{ $svgCy }}" r="{{ $diskR }}" fill="#0b0f19" clip-path="url(#disk-clip-{{ $transit->id }})" fill-opacity="0.85"/>
                                    @endif

                                    {{-- Disk outline --}}
                                    <circle cx="{{ $svgCx }}" cy="{{ $svgCy }}" r="{{ $diskR }}" fill="none"
                                        stroke="{{ $transit->type === 'sun' ? '#f59e0b' : '#94a3b8' }}"
                                        stroke-width="1.5" stroke-opacity="0.6"/>

                                    {{-- Cardinal crosshairs (faint) --}}
                                    <line x1="{{ $svgCx - $diskR - 12 }}" y1="{{ $svgCy }}" x2="{{ $svgCx + $diskR + 12 }}" y2="{{ $svgCy }}"
                                          stroke="#334155" stroke-width="0.8" stroke-dasharray="2 3"/>
                                    <line x1="{{ $svgCx }}" y1="{{ $svgCy - $diskR - 12 }}" x2="{{ $svgCx }}" y2="{{ $svgCy + $diskR + 12 }}"
                                          stroke="#334155" stroke-width="0.8" stroke-dasharray="2 3"/>

                                    {{-- ISS trajectory polyline - dashed outside disk, solid inside --}}
                                    @if($polylinePoints)
                                    {{-- Outside-disk portion: full dashed line --}}
                                    <polyline points="{{ $polylinePoints }}"
                                              fill="none" stroke="#7c3aed" stroke-width="1.5"
                                              stroke-linecap="round" stroke-linejoin="round"
                                              stroke-dasharray="3 4"
                                              marker-end="url(#arrow-{{ $transit->id }})"/>
                                    {{-- Inside-disk: solid bright clip --}}
                                    <polyline points="{{ $polylinePoints }}"
                                              fill="none" stroke="#d8b4fe" stroke-width="2.5"
                                              stroke-linecap="round" stroke-linejoin="round"
                                              clip-path="url(#disk-clip-{{ $transit->id }})"/>
                                    @endif

                                    {{-- Closest-approach dot --}}
                                    <circle cx="{{ $closestSvgX }}" cy="{{ $closestSvgY }}" r="4" fill="#d8b4fe" stroke="#7c3aed" stroke-width="1.5"/>
                                    {{-- Small perpendicular tick showing miss distance --}}
                                    <line x1="{{ $closestSvgX }}" y1="{{ $closestSvgY }}"
                                          x2="{{ $svgCx }}" y2="{{ $svgCy }}"
                                          stroke="#e879f9" stroke-width="1" stroke-dasharray="1.5 2" stroke-opacity="0.6"/>

                                    {{-- Labels --}}
                                    <text x="{{ $svgCx }}" y="{{ $svgCy - $diskR - 3 }}" font-size="7"
                                          fill="#94a3b8" text-anchor="middle" font-family="monospace">N</text>
                                    <text x="{{ $svgCx + $diskR + 4 }}" y="{{ $svgCy + 2.5 }}" font-size="7"
                                          fill="#94a3b8" text-anchor="start" font-family="monospace">E</text>
                                </svg>

                                {{-- Exact transit badge --}}
                                @if($transit->is_exact_transit)
                                <div class="absolute top-2 left-2 text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-green-900/70 text-green-300 border border-green-700">
                                    ✓ Transit
                                </div>
                                @endif

                                @if($transit->cloud_cover_percent !== null)
                                    @if($transit->cloud_cover_percent >= 90)
                                    <div class="absolute top-2 right-2 text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-amber-950/80 text-amber-300 border border-amber-600 flex items-center gap-1">
                                        <span>⚠️ Cloudy ({{ $transit->cloud_cover_percent }}%)</span>
                                    </div>
                                    @elseif($transit->cloud_cover_percent < 30)
                                    <div class="absolute top-2 right-2 text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-emerald-950/80 text-emerald-300 border border-emerald-600 flex items-center gap-1">
                                        <span>🌤️ Clear ({{ $transit->cloud_cover_percent }}%)</span>
                                    </div>
                                    @else
                                    <div class="absolute top-2 right-2 text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-slate-900/80 text-slate-300 border border-slate-700 flex items-center gap-1">
                                        <span>☁️ {{ $transit->cloud_cover_percent }}% Cloud</span>
                                    </div>
                                    @endif
                                @endif

                                {{-- Enlarge hint --}}
                                <div class="absolute inset-0 flex items-end justify-end p-2 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                                    <div class="bg-slate-900/80 backdrop-blur-sm text-slate-300 text-[9px] font-semibold px-1.5 py-0.5 rounded flex items-center gap-1">
                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                                        Enlarge
                                    </div>
                                </div>
                            </button>

                            {{-- Text metadata below diagram --}}
                            <div class="p-3 space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-full {{ $transit->type === 'sun' ? 'bg-amber-950/60 text-amber-300 border border-amber-800' : 'bg-slate-950 text-blue-300 border border-slate-700' }}">
                                        {{ $transit->type === 'sun' ? '☀️ Solar' : '🌙 Lunar' }}
                                    </span>
                                    <span class="text-[10px] text-slate-400">{{ \Carbon\Carbon::parse($transit->time)->timezone(config('app.timezone', 'UTC'))->format('l, M jS') }}</span>
                                </div>
                                @if($transit->type === 'moon')
                                    @php $moon = $transit->getMoonPhase(); @endphp
                                    <div class="flex items-center justify-between text-[10px] text-slate-400 pt-0.5">
                                        <span>Moon Phase:</span>
                                        <span class="font-semibold text-slate-300 flex items-center gap-1">{{ $moon['emoji'] }} {{ $moon['name'] }} ({{ $moon['illumination'] }}%)</span>
                                    </div>
                                @endif
                                <div class="text-purple-300 font-extrabold text-lg leading-none">
                                    {{ \Carbon\Carbon::parse($transit->time)->timezone(config('app.timezone', 'UTC'))->format('H:i:s') }}
                                </div>
                                <div class="text-slate-400 text-xs flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    {{ $transit->location->name }}
                                </div>
                                <div class="text-[10px] text-slate-500 space-y-0.5 pt-1 border-t border-slate-800/80">
                                    <div class="flex justify-between">
                                        <span>Separation:</span>
                                        <span class="font-bold {{ $transit->is_exact_transit ? 'text-green-400' : 'text-slate-300' }}">{{ $transit->separation_degrees }}°</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Alt / Az:</span>
                                        <span class="font-bold text-slate-300">{{ $transit->altitude_degrees }}° / {{ $transit->azimuth_degrees }}°</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Cloud Cover:</span>
                                        <span class="font-bold {{ $transit->cloud_cover_percent === null ? 'text-slate-400' : ($transit->cloud_cover_percent >= 90 ? 'text-amber-400' : ($transit->cloud_cover_percent < 30 ? 'text-emerald-400' : 'text-slate-300')) }}">
                                            {{ $transit->cloud_cover_percent !== null ? $transit->cloud_cover_percent . '%' : 'Pending' }}
                                        </span>
                                    </div>
                                </div>
                                @if($transit->cloud_cover_percent !== null)
                                    <div class="pt-1.5 border-t border-slate-800/80">
                                        @if($transit->cloud_cover_percent >= 90)
                                            <div class="px-2 py-1 rounded bg-amber-950/70 border border-amber-600/40 text-amber-300 text-[9px] flex items-center gap-1 font-semibold">
                                                <svg class="w-3 h-3 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                <span>Poor viewing: {{ $transit->cloud_cover_percent }}% cloud</span>
                                            </div>
                                        @elseif($transit->cloud_cover_percent < 30)
                                            <div class="px-2 py-1 rounded bg-emerald-950/50 border border-emerald-600/30 text-emerald-300 text-[9px] flex items-center gap-1 font-medium">
                                                <span>🌤️ Clear skies: {{ $transit->cloud_cover_percent }}% cloud</span>
                                            </div>
                                        @else
                                            <div class="px-2 py-1 rounded bg-slate-800/60 border border-slate-700/50 text-slate-300 text-[9px] flex items-center gap-1 font-medium">
                                                <span>☁️ {{ $transit->cloud_cover_percent }}% cloud cover</span>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="pt-1.5 border-t border-slate-800/80">
                                        <div class="px-2 py-1 rounded bg-slate-800/40 border border-slate-800 text-slate-400 text-[9px] flex items-center gap-1">
                                            <span>☁️ Forecast pending next update</span>
                                        </div>
                                    </div>
                                @endif

                                {{-- Card Quick Actions --}}
                                @php
                                    $tTimeIso = \Carbon\Carbon::parse($transit->time)->utc()->format('Ymd\THis\Z');
                                    $tEndIso = \Carbon\Carbon::parse($transit->time)->addMinutes(15)->utc()->format('Ymd\THis\Z');
                                    $tTypeStr = $transit->type === 'sun' ? 'Solar' : 'Lunar';
                                    $gTitle = urlencode("ISS {$tTypeStr} Transit — {$transit->location->name}");
                                    $gDesc = urlencode("ISS {$tTypeStr} Transit over {$transit->location->name}.\nSeparation: {$transit->separation_degrees}° (" . ($transit->is_exact_transit ? "True Transit" : "Conjunction") . ")\nAltitude: {$transit->altitude_degrees}°, Azimuth: {$transit->azimuth_degrees}°\nModeled via Astronotify: https://astronotify.org");
                                    $gLoc = urlencode("{$transit->location->name} ({$transit->location->latitude}, {$transit->location->longitude})");
                                    $gCalUrl = "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$gTitle}&dates={$tTimeIso}/{$tEndIso}&details={$gDesc}&location={$gLoc}";
                                    $sSummary = "ISS {$tTypeStr} Transit — {$transit->location->name}";
                                    $sDateFormatted = \Carbon\Carbon::parse($transit->time)->timezone(config('app.timezone', 'UTC'))->format('l, M jS \a\t H:i:s');
                                    $sCloudStr = $transit->cloud_cover_percent !== null ? $transit->cloud_cover_percent . "% cloud" : "Pending";
                                    $sText = "🛰️ ISS {$tTypeStr} Transit over {$transit->location->name}\n📅 {$sDateFormatted}\n🎯 Separation: {$transit->separation_degrees}° (" . ($transit->is_exact_transit ? "True Transit" : "Conjunction") . ") | Alt: {$transit->altitude_degrees}°\n☁️ Forecast: {$sCloudStr}\nModeled via Astronotify: https://astronotify.org";
                                @endphp
                                <div class="mt-3 pt-2 border-t border-slate-800/80 flex items-center justify-between text-xs">
                                    <button 
                                        type="button" 
                                        @click="transitModal = {{ $transit->id }}; $el.blur()" 
                                        class="text-purple-400 hover:text-purple-300 font-semibold flex items-center gap-1 group/btn"
                                    >
                                        <span>Orbit Diagram</span>
                                        <span class="transform group-hover/btn:translate-x-0.5 transition-transform">&rarr;</span>
                                    </button>

                                    <div class="flex items-center gap-1.5">
                                        <a 
                                            href="{{ $gCalUrl }}" 
                                            target="_blank" 
                                            rel="noopener noreferrer" 
                                            title="Add to Google Calendar" 
                                            class="p-1.5 rounded-lg bg-slate-800/80 hover:bg-slate-700 text-slate-300 hover:text-blue-400 transition-colors"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </a>
                                        <button 
                                            type="button" 
                                            @click="downloadIcs(@js($sSummary), @js($transit->location->name), @js($sText), @js($tTimeIso), @js($tEndIso))" 
                                            title="Download iCal (.ics)" 
                                            class="p-1.5 rounded-lg bg-slate-800/80 hover:bg-slate-700 text-slate-300 hover:text-purple-400 transition-colors"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        </button>
                                        <button 
                                            type="button" 
                                            @click="shareTransit(@js($sSummary), @js($sText))" 
                                            title="Share pass with astronomy friends" 
                                            class="p-1.5 rounded-lg bg-slate-800/80 hover:bg-slate-700 text-slate-300 hover:text-emerald-400 transition-colors"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <div class="flex flex-col md:flex-row gap-8 items-start w-full">
            <!-- Form Card Column -->
            <div 
                :class="formOpen ? 'w-full md:w-[380px] lg:w-[420px]' : 'w-full md:w-16'"
                class="transition-all duration-300 ease-in-out shrink-0 w-full md:sticky md:top-6 self-start"
            >
                <div class="bg-slate-800/40 backdrop-blur-xl border border-slate-700/50 rounded-3xl shadow-2xl transition-all duration-300 overflow-hidden relative min-h-[56px] md:min-h-[520px] flex flex-col justify-between">
                    
                    <!-- Expanded Form Content -->
                    <div 
                        x-show="formOpen"
                        x-transition:enter="transition ease-out duration-300 delay-100"
                        x-transition:enter-start="opacity-0 transform -translate-x-2"
                        x-transition:enter-end="opacity-100 transform translate-x-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 transform translate-x-0"
                        x-transition:leave-end="opacity-0 transform -translate-x-2"
                        class="p-6 space-y-6"
                    >
                        <div class="flex justify-between items-center border-b border-slate-700/60 pb-3">
                            <h2 class="text-xl font-bold text-white">{{ $editingLocationId ? 'Edit Location' : 'Add New Location' }}</h2>
                            @if($locations->isNotEmpty())
                                <button type="button" @click="formOpen = false; $el.blur()" class="text-slate-400 hover:text-white transition-colors" title="Collapse Form">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                                </button>
                            @endif
                        </div>
                        
                        <form wire:submit="save" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-300">Location Name</label>
                                <input type="text" wire:model="name" class="mt-1 block w-full bg-slate-900/50 border border-slate-700 rounded-xl text-white focus:ring-purple-500 focus:border-purple-500 placeholder-slate-500" placeholder="e.g. Home">
                                @error('name') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <!-- GPS Location Button -->
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-1.5">Coordinates & Elevation</label>
                                <button 
                                    type="button" 
                                    @click="getGpsLocation()" 
                                    :disabled="locating" 
                                    class="w-full flex items-center justify-center gap-2 py-2.5 px-4 bg-gradient-to-r from-purple-900/50 to-blue-900/50 hover:from-purple-800/70 hover:to-blue-800/70 text-purple-200 border border-purple-500/40 rounded-xl transition-all font-medium text-sm shadow-md active:scale-98"
                                >
                                    <template x-if="!locating">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-purple-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            <span>Use My Current GPS</span>
                                        </span>
                                    </template>
                                    <template x-if="locating">
                                        <span class="flex items-center gap-2">
                                            <div class="w-4 h-4 border-2 border-purple-400 border-t-transparent rounded-full animate-spin"></div>
                                            <span x-text="locatingMsg" class="text-xs">Acquiring GPS fix...</span>
                                        </span>
                                    </template>
                                </button>
                                <p x-show="gpsError" x-text="gpsError" class="text-xs text-amber-400 mt-1" x-cloak></p>
                            </div>

                            <div class="relative flex items-center justify-center my-1">
                                <div class="border-t border-slate-700/60 w-full"></div>
                                <span class="bg-slate-800 px-2 text-[10px] font-semibold text-slate-400 uppercase tracking-wider absolute">or search by name</span>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-300">Search Town/City</label>
                                <input type="text" wire:model.live.debounce.500ms="town" class="mt-1 block w-full bg-slate-900/50 border border-slate-700 rounded-xl text-white focus:ring-purple-500 focus:border-purple-500 placeholder-slate-500" placeholder="e.g. Lancaster, UK">
                                <p class="text-xs text-slate-400 mt-1">Typing a town will auto-fill the coordinates below.</p>
                            </div>

                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-300">Latitude</label>
                                    <input type="text" wire:model="latitude" class="mt-1 block w-full bg-slate-900/50 border border-slate-700 rounded-xl text-white focus:ring-purple-500 focus:border-purple-500 placeholder-slate-500" placeholder="0.000">
                                    @error('latitude') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-300">Longitude</label>
                                    <input type="text" wire:model="longitude" class="mt-1 block w-full bg-slate-900/50 border border-slate-700 rounded-xl text-white focus:ring-purple-500 focus:border-purple-500 placeholder-slate-500" placeholder="0.000">
                                    @error('longitude') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-300" title="Elevation above sea level in meters">Elevation (m)</label>
                                    <input type="number" wire:model="elevation" class="mt-1 block w-full bg-slate-900/50 border border-slate-700 rounded-xl text-white focus:ring-purple-500 focus:border-purple-500 placeholder-slate-500" placeholder="0">
                                    @error('elevation') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-300">Bortle Light Pollution Class</label>
                                <select wire:model="bortle" class="mt-1 block w-full bg-slate-900/50 border border-slate-700 rounded-xl text-white focus:ring-purple-500 focus:border-purple-500 cursor-pointer">
                                    <option value="">Unknown / Select Class</option>
                                    <option value="1">Class 1: Excellent Dark Sky (🌑)</option>
                                    <option value="2">Class 2: Typical Truly Dark Sky (🌌)</option>
                                    <option value="3">Class 3: Rural Sky (🌌)</option>
                                    <option value="4">Class 4: Rural/Suburban Transition</option>
                                    <option value="5">Class 5: Suburban Sky</option>
                                    <option value="6">Class 6: Bright Suburban Sky</option>
                                    <option value="7">Class 7: Suburban/Urban Transition</option>
                                    <option value="8">Class 8: City Sky</option>
                                    <option value="9">Class 9: Inner-City Sky (🏙️)</option>
                                </select>
                                <p class="text-xs text-slate-400 mt-1">Don't know yours? Click <a href="https://www.lightpollutionmap.info/#zoom=10&lat={{ $latitude ?: 54 }}&lon={{ $longitude ?: -2 }}" target="_blank" class="text-purple-400 hover:text-purple-300 underline">LightPollutionMap.info</a> to look up your site.</p>
                                @error('bortle') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-300" title="Minimum night duration (sunset to sunrise)">Min Night (hrs)</label>
                                    <input type="number" wire:model="min_night_length_hours" class="mt-1 block w-full bg-slate-900/50 border border-slate-700 rounded-xl text-white focus:ring-purple-500 focus:border-purple-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-300" title="Contiguous clear hours">Clear Hrs</label>
                                    <input type="number" wire:model="min_clear_hours" class="mt-1 block w-full bg-slate-900/50 border border-slate-700 rounded-xl text-white focus:ring-purple-500 focus:border-purple-500">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-300">Max Wind (km/h)</label>
                                    <input type="number" step="0.1" wire:model="max_wind_speed" class="mt-1 block w-full bg-slate-900/50 border border-slate-700 rounded-xl text-white focus:ring-purple-500 focus:border-purple-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-300">Max Clouds (%)</label>
                                    <input type="number" wire:model="max_cloud_cover" class="mt-1 block w-full bg-slate-900/50 border border-slate-700 rounded-xl text-white focus:ring-purple-500 focus:border-purple-500">
                                </div>
                            </div>

                            <div class="border-t border-slate-700 pt-4 space-y-3">
                                <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Notification Settings</span>
                                
                                <label class="relative flex items-start cursor-pointer select-none">
                                    <div class="flex items-center h-5">
                                        <input type="checkbox" wire:model="notify_stargazing_alerts" class="w-4 h-4 rounded border-slate-700 bg-slate-900/50 text-purple-600 focus:ring-purple-500 cursor-pointer">
                                    </div>
                                    <div class="ms-3 text-sm">
                                        <span class="font-medium text-slate-200">Stargazing Weather Alerts 🔔</span>
                                        <span class="block text-xs text-slate-400">Get notified when stargazing conditions are optimal.</span>
                                    </div>
                                </label>

                                <label class="relative flex items-start cursor-pointer select-none">
                                    <div class="flex items-center h-5">
                                        <input type="checkbox" wire:model="notify_iss_sun_transit" class="w-4 h-4 rounded border-slate-700 bg-slate-900/50 text-purple-600 focus:ring-purple-500 cursor-pointer">
                                    </div>
                                    <div class="ms-3 text-sm">
                                        <span class="font-medium text-slate-200">Notify of ISS Solar Transits ☀️</span>
                                        <span class="block text-xs text-slate-400">Get alerted when the ISS passes in front of the Sun.</span>
                                    </div>
                                </label>

                                <label class="relative flex items-start cursor-pointer select-none">
                                    <div class="flex items-center h-5">
                                        <input type="checkbox" wire:model="notify_iss_moon_transit" class="w-4 h-4 rounded border-slate-700 bg-slate-900/50 text-purple-600 focus:ring-purple-500 cursor-pointer">
                                    </div>
                                    <div class="ms-3 text-sm">
                                        <span class="font-medium text-slate-200">Notify of ISS Lunar Transits 🌙</span>
                                        <span class="block text-xs text-slate-400">Get alerted when the ISS passes in front of the Moon.</span>
                                    </div>
                                </label>
                            </div>

                            <div class="pt-4 flex flex-col space-y-3">
                                <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-500 hover:to-purple-500 text-white font-bold py-3 px-4 rounded-xl shadow-lg transition-all transform hover:scale-[1.02]">
                                    {{ $editingLocationId ? 'Update Location' : 'Save Location' }}
                                </button>
                                @if($editingLocationId)
                                    <button type="button" wire:click="cancelEdit" class="w-full bg-slate-700 hover:bg-slate-600 text-white font-bold py-3 px-4 rounded-xl shadow-lg transition-colors">
                                        Cancel Editing
                                    </button>
                                @endif
                            </div>
                        </form>
                    </div>

                    <!-- Collapsed Tab Content -->
                    <button 
                        type="button"
                        x-show="!formOpen" 
                        @click="formOpen = true; $el.blur()"
                        x-transition:enter="transition ease-out duration-300 delay-150" 
                        x-transition:enter-start="opacity-0" 
                        x-transition:enter-end="opacity-100" 
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="absolute inset-0 w-full h-full flex flex-row md:flex-col items-center justify-center p-4 text-purple-400 hover:text-purple-300 hover:bg-slate-800/60 transition-all cursor-pointer group"
                    >
                        <div class="flex flex-row md:flex-col items-center justify-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-purple-900/40 border border-purple-500/30 flex items-center justify-center group-hover:scale-110 group-hover:border-purple-400 transition-all shrink-0">
                                <svg class="w-5 h-5 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                            </div>
                            <span class="text-xs font-bold tracking-widest uppercase text-slate-400 group-hover:text-purple-300 transition-colors whitespace-nowrap md:[writing-mode:vertical-lr] md:rotate-180">
                                Add Location
                            </span>
                        </div>
                    </button>

                </div>
            </div>

            <!-- Locations List -->
            <div class="flex-1 space-y-6 transition-all duration-500 w-full">
                @forelse($locations as $location)
                    <div class="bg-slate-800/30 backdrop-blur-xl border border-slate-700/50 rounded-3xl p-6 shadow-xl hover:bg-slate-800/50 transition-all space-y-6">
                        <div class="flex flex-col justify-between items-start md:flex-row md:items-center">
                            <div>
                                <h3 class="text-2xl font-bold text-white">{{ $location->name }}</h3>
                                <div class="text-slate-400 text-sm mt-1 mb-3 flex flex-wrap items-center gap-y-2 gap-x-4">
                                    <span>Lat: <span class="text-slate-200">{{ $location->latitude }}</span></span>
                                    <span>Lon: <span class="text-slate-200">{{ $location->longitude }}</span></span>
                                    <span>Elev: <span class="text-slate-200">{{ $location->elevation }}m</span></span>
                                    @if($location->bortle)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-slate-900/80 border border-slate-700 text-xs text-slate-300" title="Bortle Light Pollution Class">
                                            🌌 Bortle {{ $location->bortle }}
                                        </span>
                                    @endif
                                    <a href="https://www.lightpollutionmap.info/#zoom=12&lat={{ $location->latitude }}&lon={{ $location->longitude }}" target="_blank" class="text-xs text-purple-400 hover:text-purple-300 hover:underline flex items-center">
                                        🗺️ Light Pollution Map &rarr;
                                    </a>
                                </div>
                                <div class="flex flex-wrap gap-2 mt-1 mb-4">
                                    @if($location->notify_stargazing_alerts)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-950/40 text-emerald-300 border border-emerald-800/60 shadow">
                                            🔔 Weather Alerts Enabled
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-950/40 text-red-300 border border-red-800/60 shadow">
                                            🔕 Weather Alerts Disabled
                                        </span>
                                    @endif
                                    @if($location->notify_iss_sun_transit)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-950/40 text-amber-300 border border-amber-800/60 shadow">
                                            ☀️ ISS Solar Transit
                                        </span>
                                    @endif
                                    @if($location->notify_iss_moon_transit)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-950/60 text-blue-300 border border-slate-700 shadow">
                                            🌙 ISS Lunar Transit
                                        </span>
                                    @endif
                                </div>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs text-slate-300">
                                    <div class="bg-slate-900/60 p-2 rounded-lg border border-slate-700">🌙 Min Night: <span class="font-bold text-blue-300">{{ $location->min_night_length_hours }}h</span></div>
                                    <div class="bg-slate-900/60 p-2 rounded-lg border border-slate-700">✨ Clear: <span class="font-bold text-purple-300">{{ $location->min_clear_hours }}h+</span></div>
                                    <div class="bg-slate-900/60 p-2 rounded-lg border border-slate-700">💨 Wind: <span class="font-bold text-teal-300"><{{ $location->max_wind_speed }}</span></div>
                                    <div class="bg-slate-900/60 p-2 rounded-lg border border-slate-700">☁️ Clouds: <span class="font-bold text-gray-300"><{{ $location->max_cloud_cover }}%</span></div>
                                </div>
                            </div>
                            <div class="mt-4 md:mt-0 flex shrink-0 border border-slate-700 p-1 space-x-1 rounded-xl bg-slate-900/50">
                                <button 
                                    wire:click="sendTestEmail({{ $location->id }})" 
                                    wire:loading.attr="disabled"
                                    x-on:click="$el.blur()" 
                                    class="p-2 text-yellow-400 hover:text-yellow-300 hover:bg-yellow-900/30 rounded-lg transition-colors flex items-center justify-center" 
                                    title="Send Test Email"
                                >
                                    <svg wire:loading.remove wire:target="sendTestEmail({{ $location->id }})" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                    <div wire:loading wire:target="sendTestEmail({{ $location->id }})" class="w-6 h-6 border-2 border-yellow-400 border-t-transparent rounded-full animate-spin"></div>
                                </button>
                                <button wire:click="edit({{ $location->id }})" x-on:click="$el.blur()" class="p-2 text-blue-400 hover:text-blue-300 hover:bg-blue-900/30 rounded-lg transition-colors" title="Edit">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </button>
                                <button wire:click="delete({{ $location->id }})" wire:confirm="Are you sure you want to delete this location?" class="p-2 text-red-400 hover:text-red-300 hover:bg-red-900/30 rounded-lg transition-colors" title="Delete">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </div>
                        </div>

                        <!-- 7-Day Forecast Section -->
                        <div x-data="{ activeDay: null }" class="border-t border-slate-700/60 pt-4">
                            <h4 class="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-3">📅 7-Day Forecast</h4>
                            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-2">
                                @forelse($location->conditions as $index => $cond)
                                    @php
                                        $maxCloud = 0;
                                        $maxWind = 0;
                                        if (is_array($cond->forecast_data)) {
                                            foreach ($cond->forecast_data as $h) {
                                                $maxCloud = max($maxCloud, $h['cloud'] ?? 0);
                                                $maxWind = max($maxWind, $h['wind'] ?? 0);
                                            }
                                        }
                                    @endphp
                                    <button 
                                        type="button"
                                        @click="activeDay = (activeDay === {{ $index }} ? null : {{ $index }}); $el.blur()"
                                        :class="activeDay === {{ $index }} ? 'ring-2 ring-purple-500 bg-slate-900 border-purple-500/50 text-left' : 'bg-slate-900/50 border-slate-700/60 hover:bg-slate-900 text-left'"
                                        class="border rounded-2xl p-3 flex flex-col items-center justify-between text-center min-h-[140px] w-full transition-all cursor-pointer"
                                    >
                                        <span class="text-xs font-bold text-slate-400">{{ \Carbon\Carbon::parse($cond->date)->format('D d') }}</span>
                                        <div class="my-1.5 flex items-center justify-center space-x-1.5">
                                            @if($cond->is_optimal)
                                                <span class="text-2xl" title="Optimal conditions! Clear night.">✨</span>
                                            @else
                                                <span class="text-2xl" title="Sub-optimal stargazing conditions.">☁️</span>
                                            @endif
                                            @php $moon = $cond->getMoonPhase(); @endphp
                                            <span class="text-xl" title="{{ $moon['name'] }} ({{ $moon['illumination'] }}% illuminated)">{{ $moon['emoji'] }}</span>
                                        </div>
                                        <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-full {{ $cond->is_optimal ? 'bg-purple-950/60 text-purple-300 border border-purple-800' : 'bg-slate-800 text-slate-400 border border-slate-700' }}">
                                            {{ $cond->is_optimal ? 'Clear' : 'Poor' }}
                                        </span>
                                        <div class="text-[10px] text-slate-400 space-y-0.5 mt-2 w-full border-t border-slate-800 pt-1.5">
                                            <div class="flex items-center justify-between px-1">
                                                <span>☁️ Max:</span>
                                                <span class="font-bold text-slate-300">{{ $maxCloud }}%</span>
                                            </div>
                                            <div class="flex items-center justify-between px-1">
                                                <span>💨 Max:</span>
                                                <span class="font-bold text-slate-300">{{ round($maxWind) }}k</span>
                                            </div>
                                        </div>
                                    </button>
                                @empty
                                    <div class="col-span-full py-4 text-center text-slate-500 text-xs">
                                        No forecast data loaded. Run weather:fetch to populate.
                                    </div>
                                @endforelse
                            </div>

                            <!-- Hourly Breakdown Panel -->
                            @foreach($location->conditions as $index => $cond)
                                @if(is_array($cond->forecast_data) && count($cond->forecast_data) > 0)
                                    <div 
                                        x-show="activeDay === {{ $index }}" 
                                        x-collapse 
                                        x-cloak
                                    >
                                        <div class="mt-4 bg-slate-950/60 border border-slate-800 rounded-2xl p-4 space-y-3 text-left">
                                            <div class="flex justify-between items-center border-b border-slate-800 pb-2">
                                                <h5 class="text-xs font-bold text-purple-400 uppercase tracking-wider">
                                                    Hourly Forecast breakdown for {{ \Carbon\Carbon::parse($cond->date)->format('l, M jS') }}
                                                </h5>
                                                <span class="text-[10px] text-slate-400">Values evaluated for clear-sky slots</span>
                                            </div>

                                            @php $moon = $cond->getMoonPhase(); @endphp
                                            <div class="flex items-center space-x-3 bg-slate-900/30 border border-slate-800/80 rounded-xl p-3 text-xs text-slate-300">
                                                <span class="text-3xl select-none">{{ $moon['emoji'] }}</span>
                                                <div class="flex-1">
                                                    <span class="block font-bold text-slate-200">{{ $moon['name'] }}</span>
                                                    <span class="block text-[10px] text-slate-500">Illumination: <span class="text-purple-300 font-semibold">{{ $moon['illumination'] }}%</span></span>
                                                </div>
                                                <div class="text-[10px] text-slate-400 max-w-[250px] text-right hidden sm:block">
                                                    @if($moon['illumination'] >= 70)
                                                        🌕 <span class="text-amber-300 font-medium">Bright moon</span> will affect deep-sky contrast. Good for planets, double stars, or the moon itself.
                                                    @else
                                                        🌌 <span class="text-emerald-300 font-medium font-semibold">Dark skies</span>! Excellent window for faint nebulae, galaxies, and star clusters.
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-2">
                                                @foreach($cond->forecast_data as $h)
                                                    @php
                                                        $isHourOptimal = ($h['cloud'] <= $location->max_cloud_cover && $h['wind'] <= $location->max_wind_speed);
                                                    @endphp
                                                    <div class="bg-slate-900/40 border {{ $isHourOptimal ? 'border-purple-500/20' : 'border-slate-800' }} rounded-xl p-2 text-center text-xs space-y-1">
                                                        <span class="font-bold text-slate-300 block">{{ $h['time'] }}</span>
                                                        <div class="flex justify-between text-[10px] px-1 text-slate-400">
                                                            <span>Clouds:</span>
                                                            <span class="{{ $h['cloud'] <= $location->max_cloud_cover ? 'text-green-400 font-bold' : 'text-red-400' }}">{{ $h['cloud'] }}%</span>
                                                        </div>
                                                        <div class="flex justify-between text-[10px] px-1 text-slate-400">
                                                            <span>Wind:</span>
                                                            <span class="{{ $h['wind'] <= $location->max_wind_speed ? 'text-green-400 font-bold' : 'text-red-400' }}">{{ round($h['wind']) }}</span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        <!-- ISS Passes Section -->
                        <div x-data="{ open: false }" class="border-t border-slate-700/60 pt-4">
                            <button 
                                type="button"
                                @click="open = !open; if(open) { $wire.loadPasses({{ $location->id }}) }; $el.blur()" 
                                class="w-full flex justify-between items-center text-left focus:outline-none focus:ring-0"
                            >
                                <h4 class="text-sm font-semibold text-slate-400 uppercase tracking-wider flex items-center">
                                    <span class="mr-2">🛰️</span> Upcoming ISS Passes
                                </h4>
                                <div class="flex items-center space-x-2">
                                    <div wire:loading wire:target="loadPasses({{ $location->id }})" class="w-3.5 h-3.5 border-2 border-slate-400 border-t-transparent rounded-full animate-spin"></div>
                                    <svg :class="open ? 'transform rotate-180' : ''" class="w-4 h-4 text-slate-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                </div>
                            </button>
                            
                            <div x-show="open" x-collapse x-cloak class="mt-3">
                                @if(isset($loadedPasses[$location->id]))
                                    @if(empty($loadedPasses[$location->id]))
                                        <div class="text-xs text-slate-500 py-2">No upcoming visible passes in the next 7 days.</div>
                                    @else
                                        <div class="overflow-x-auto rounded-2xl border border-slate-800 bg-slate-950/40">
                                            <table class="w-full text-left border-collapse">
                                                <thead>
                                                    <tr class="border-b border-slate-800 text-[10px] text-slate-500 font-bold uppercase tracking-wider">
                                                        <th class="py-2.5 px-3">Date</th>
                                                        <th class="py-2.5 px-3">Start Time (AOS)</th>
                                                        <th class="py-2.5 px-3">End Time (LOS)</th>
                                                        <th class="py-2.5 px-3 text-center">Duration</th>
                                                        <th class="py-2.5 px-3 text-center">Max Elevation</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($loadedPasses[$location->id] as $pass)
                                                        <tr class="border-b border-slate-800/40 hover:bg-slate-900/20 text-xs text-slate-300 transition-colors">
                                                            <td class="py-2.5 px-3 font-semibold text-slate-200">{{ $pass['date'] }}</td>
                                                            <td class="py-2.5 px-3 text-purple-300 font-semibold">{{ $pass['aos'] }}</td>
                                                            <td class="py-2.5 px-3 font-mono text-[11px]">{{ $pass['los'] }}</td>
                                                            <td class="py-2.5 px-3 text-center text-slate-400">{{ $pass['duration'] }} min</td>
                                                            <td class="py-2.5 px-3 text-center">
                                                                <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold {{ $pass['max_el'] >= 40 ? 'bg-green-950/60 text-green-300 border border-green-800' : ($pass['max_el'] >= 20 ? 'bg-amber-950/60 text-amber-300 border border-amber-800' : 'bg-slate-950 text-slate-400 border border-slate-700') }}">
                                                                    {{ $pass['max_el'] }}°
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                @else
                                    <div class="text-xs text-slate-500 py-3 flex items-center justify-center gap-2 bg-slate-950/20 rounded-2xl border border-dashed border-slate-800">
                                        <div class="w-3.5 h-3.5 border-2 border-slate-400 border-t-transparent rounded-full animate-spin"></div>
                                        <span>Calculating orbital trajectory...</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center p-10 bg-slate-800/20 backdrop-blur-sm border border-dashed border-slate-700/80 rounded-3xl text-center">
                        <div class="w-16 h-16 rounded-full bg-purple-900/30 border border-purple-500/30 flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-1">No locations saved yet</h3>
                        <p class="text-sm text-slate-400 max-w-sm mb-6">Add a location to begin receiving stargazing condition alerts and ISS transit forecasts.</p>
                        <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                            <button 
                                type="button" 
                                @click="getGpsLocation()" 
                                :disabled="locating" 
                                class="inline-flex items-center justify-center gap-2 py-3 px-6 bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-500 hover:to-blue-500 text-white rounded-xl font-bold shadow-lg shadow-purple-900/30 transition-all active:scale-95"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>Add My Current GPS</span>
                            </button>
                            <button 
                                type="button" 
                                @click="formOpen = true; $el.blur()" 
                                class="py-3 px-6 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-xl font-semibold transition-all"
                            >
                                Enter Manually
                            </button>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ====================================================
         Transit Path Modal — one hidden panel per transit
         ==================================================== --}}
    @if($upcomingTransits->count() > 0)
    <div
        x-show="transitModal !== null"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click.self="transitModal = null"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md"
        x-cloak
    >
        @foreach($upcomingTransits as $transit)
        @php
            // Recalculate SVG geometry for the modal (larger canvas: 400×400, disk r=130)
            $mCx = 200; $mCy = 200; $mR = 130;
            $mScale = $mR / 0.27;

            $mRawPts = $transit->path_points ?? [];
            $mClosestPt = collect($mRawPts)->sortBy(fn($p) => abs($p['dx']))->first()
                          ?? ['dx' => 0, 'dy' => -($transit->separation_degrees)];
            $mClosestX = round($mCx + ($mClosestPt['dx'] * $mScale), 1);
            $mClosestY = round($mCy - ($mClosestPt['dy'] * $mScale), 1);

            if (count($mRawPts) >= 2) {
                $mFirst = $mRawPts[0]; $mLast = $mRawPts[count($mRawPts) - 1];
                $mvx = $mLast['dx'] - $mFirst['dx'];
                $mvy = $mLast['dy'] - $mFirst['dy'];
            } else { $mvx = 1.0; $mvy = 0.0; }
            $mvLen = sqrt($mvx * $mvx + $mvy * $mvy);
            if ($mvLen < 0.0001) { $mvx = 1.0; $mvy = 0.0; $mvLen = 1.0; }
            $mvx /= $mvLen; $mvy /= $mvLen;

            $mExtend = 250.0;
            $mX1 = round($mClosestX - $mvx * $mScale * $mExtend, 1);
            $mY1 = round($mClosestY + $mvy * $mScale * $mExtend, 1);
            $mX2 = round($mClosestX + $mvx * $mScale * $mExtend, 1);
            $mY2 = round($mClosestY - $mvy * $mScale * $mExtend, 1);
            $mPolyPoints = "{$mX1},{$mY1} {$mX2},{$mY2}";

            // Separation arc in SVG pixels
            $mSepPx = round(($transit->separation_degrees / 0.27) * $mR, 1);
        @endphp

        <div
            x-show="transitModal === {{ $transit->id }}"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative bg-slate-900 border {{ $transit->type === 'sun' ? 'border-amber-500/30' : 'border-blue-500/30' }} rounded-3xl shadow-2xl overflow-hidden flex flex-col md:flex-row w-full max-w-3xl max-h-[92vh] overflow-y-auto"
        >
            {{-- Close button --}}
            <button @click="transitModal = null; $el.blur()" class="absolute top-3 right-3 z-10 w-8 h-8 flex items-center justify-center rounded-full bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            {{-- Large SVG panel --}}
            <div class="bg-slate-950 flex items-center justify-center p-4 md:w-[420px] shrink-0">
                <svg viewBox="0 0 400 400" class="w-full max-w-[380px]">
                    <defs>
                        @if($transit->type === 'sun')
                        <radialGradient id="mbody-{{ $transit->id }}" cx="40%" cy="35%">
                            <stop offset="0%"   stop-color="#fef08a"/>
                            <stop offset="40%"  stop-color="#fbbf24"/>
                            <stop offset="80%"  stop-color="#ea580c"/>
                            <stop offset="100%" stop-color="#b45309" stop-opacity="0.7"/>
                        </radialGradient>
                        <filter id="mglow-{{ $transit->id }}">
                            <feGaussianBlur stdDeviation="10" result="blur"/>
                            <feMerge><feMergeNode in="blur"/><feMergeNode in="SourceGraphic"/></feMerge>
                        </filter>
                        @else
                        <radialGradient id="mbody-{{ $transit->id }}" cx="35%" cy="30%">
                            <stop offset="0%"   stop-color="#e2e8f0"/>
                            <stop offset="60%"  stop-color="#94a3b8"/>
                            <stop offset="100%" stop-color="#475569"/>
                        </radialGradient>
                        @endif
                        <clipPath id="mdisk-clip-{{ $transit->id }}">
                            <circle cx="{{ $mCx }}" cy="{{ $mCy }}" r="{{ $mR }}"/>
                        </clipPath>
                        <marker id="marrow-{{ $transit->id }}" markerWidth="8" markerHeight="8" refX="4" refY="4" orient="auto">
                            <path d="M0,0 L8,4 L0,8 Z" fill="#a855f7"/>
                        </marker>
                    </defs>

                    {{-- Background starfield --}}
                    <rect width="400" height="400" fill="#020617"/>
                    <circle cx="30"  cy="50"  r="0.8" fill="white" fill-opacity="0.6"/>
                    <circle cx="80"  cy="20"  r="1"   fill="white" fill-opacity="0.5"/>
                    <circle cx="350" cy="80"  r="0.8" fill="white" fill-opacity="0.7"/>
                    <circle cx="370" cy="340" r="1.2" fill="white" fill-opacity="0.4"/>
                    <circle cx="20"  cy="370" r="0.8" fill="white" fill-opacity="0.6"/>
                    <circle cx="320" cy="30"  r="0.6" fill="white" fill-opacity="0.5"/>
                    <circle cx="60"  cy="300" r="1"   fill="white" fill-opacity="0.4"/>
                    <circle cx="380" cy="200" r="0.7" fill="white" fill-opacity="0.6"/>

                    @if($transit->type === 'sun')
                    {{-- Solar corona rings --}}
                    <circle cx="{{ $mCx }}" cy="{{ $mCy }}" r="{{ $mR + 30 }}" fill="none" stroke="#fbbf24" stroke-width="2"   stroke-opacity="0.08"/>
                    <circle cx="{{ $mCx }}" cy="{{ $mCy }}" r="{{ $mR + 20 }}" fill="none" stroke="#f59e0b" stroke-width="2"   stroke-opacity="0.14"/>
                    <circle cx="{{ $mCx }}" cy="{{ $mCy }}" r="{{ $mR + 10 }}" fill="none" stroke="#f59e0b" stroke-width="2.5" stroke-opacity="0.25"/>
                    <circle cx="{{ $mCx }}" cy="{{ $mCy }}" r="{{ $mR }}" fill="url(#mbody-{{ $transit->id }})" filter="url(#mglow-{{ $transit->id }})"/>
                    @else
                    <circle cx="{{ $mCx }}" cy="{{ $mCy }}" r="{{ $mR }}" fill="url(#mbody-{{ $transit->id }})"/>
                    {{-- Craters (scaled up) --}}
                    <circle cx="{{ $mCx - 46 }}" cy="{{ $mCy - 38 }}" r="16" fill="#64748b" fill-opacity="0.5"/>
                    <circle cx="{{ $mCx + 26 }}" cy="{{ $mCy + 46 }}" r="23" fill="#64748b" fill-opacity="0.4"/>
                    <circle cx="{{ $mCx - 13 }}" cy="{{ $mCy + 20 }}" r="13" fill="#64748b" fill-opacity="0.35"/>
                    <circle cx="{{ $mCx + 52 }}" cy="{{ $mCy - 26 }}" r="10" fill="#64748b" fill-opacity="0.45"/>
                    <circle cx="{{ $mCx - 52 }}" cy="{{ $mCy + 13 }}" r="8"  fill="#64748b" fill-opacity="0.5"/>
                    <circle cx="{{ $mCx + 15 }}" cy="{{ $mCy - 60 }}" r="6"  fill="#64748b" fill-opacity="0.4"/>

                    {{-- Dynamic Moon Phase shadow --}}
                    @php
                        $moon = $transit->getMoonPhase();
                        $P = $moon['phase'];
                        if ($P <= 0.5) {
                            $mShadowCx = $mCx - (2 * $mR * ($P / 0.5));
                        } else {
                            $mShadowCx = ($mCx + 2 * $mR) - (2 * $mR * (($P - 0.5) / 0.5));
                        }
                    @endphp
                    <circle cx="{{ $mShadowCx }}" cy="{{ $mCy }}" r="{{ $mR }}" fill="#020617" clip-path="url(#mdisk-clip-{{ $transit->id }})" fill-opacity="0.85"/>
                    @endif

                    {{-- Disk outline --}}
                    <circle cx="{{ $mCx }}" cy="{{ $mCy }}" r="{{ $mR }}" fill="none"
                        stroke="{{ $transit->type === 'sun' ? '#f59e0b' : '#94a3b8' }}"
                        stroke-width="2" stroke-opacity="0.7"/>

                    {{-- Crosshairs --}}
                    <line x1="{{ $mCx - $mR - 25 }}" y1="{{ $mCy }}" x2="{{ $mCx + $mR + 25 }}" y2="{{ $mCy }}"
                          stroke="#1e3a5f" stroke-width="1" stroke-dasharray="3 5"/>
                    <line x1="{{ $mCx }}" y1="{{ $mCy - $mR - 25 }}" x2="{{ $mCx }}" y2="{{ $mCy + $mR + 25 }}"
                          stroke="#1e3a5f" stroke-width="1" stroke-dasharray="3 5"/>

                    {{-- Separation arc (dashed circle at miss distance) --}}
                    @if($mSepPx > 0 && $mSepPx < $mR * 2)
                    <circle cx="{{ $mCx }}" cy="{{ $mCy }}" r="{{ $mSepPx }}"
                            fill="none" stroke="#6366f1" stroke-width="0.8" stroke-dasharray="4 6" stroke-opacity="0.5"/>
                    @endif

                    {{-- Trajectory --}}
                    <polyline points="{{ $mPolyPoints }}"
                              fill="none" stroke="#7c3aed" stroke-width="2"
                              stroke-dasharray="4 5" stroke-linecap="round"
                              marker-end="url(#marrow-{{ $transit->id }})"/>
                    <polyline points="{{ $mPolyPoints }}"
                              fill="none" stroke="#c4b5fd" stroke-width="3.5"
                              stroke-linecap="round" stroke-linejoin="round"
                              clip-path="url(#mdisk-clip-{{ $transit->id }})"/>

                    {{-- Closest-approach dot --}}
                    <circle cx="{{ $mClosestX }}" cy="{{ $mClosestY }}" r="7" fill="#d8b4fe" stroke="#7c3aed" stroke-width="2.5"/>
                    <circle cx="{{ $mClosestX }}" cy="{{ $mClosestY }}" r="3" fill="white"/>
                    {{-- Miss-distance line to centre --}}
                    <line x1="{{ $mClosestX }}" y1="{{ $mClosestY }}" x2="{{ $mCx }}" y2="{{ $mCy }}"
                          stroke="#e879f9" stroke-width="1.5" stroke-dasharray="2 3" stroke-opacity="0.7"/>

                    {{-- Cardinal labels --}}
                    <text x="{{ $mCx }}" y="{{ $mCy - $mR - 8 }}" font-size="12" fill="#64748b" text-anchor="middle" font-family="monospace" font-weight="bold">N</text>
                    <text x="{{ $mCx }}" y="{{ $mCy + $mR + 18 }}" font-size="12" fill="#64748b" text-anchor="middle" font-family="monospace" font-weight="bold">S</text>
                    <text x="{{ $mCx + $mR + 10 }}" y="{{ $mCy + 4 }}" font-size="12" fill="#64748b" text-anchor="start"  font-family="monospace" font-weight="bold">E</text>
                    <text x="{{ $mCx - $mR - 10 }}" y="{{ $mCy + 4 }}" font-size="12" fill="#64748b" text-anchor="end"    font-family="monospace" font-weight="bold">W</text>

                    {{-- Scale bar: 0.5° --}}
                    @php $scalePx = round(0.5 / 0.27 * $mR, 1); @endphp
                    <line x1="{{ $mCx - $mR }}" y1="{{ $mCy + $mR + 28 }}"
                          x2="{{ $mCx - $mR + $scalePx }}" y2="{{ $mCy + $mR + 28 }}"
                          stroke="#475569" stroke-width="2" stroke-linecap="square"/>
                    <text x="{{ $mCx - $mR + $scalePx / 2 }}" y="{{ $mCy + $mR + 40 }}"
                          font-size="10" fill="#64748b" text-anchor="middle" font-family="monospace">0.5°</text>
                </svg>
            </div>

            {{-- Info panel --}}
            <div class="p-6 flex flex-col justify-between flex-1 min-w-0">
                <div class="space-y-3">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-sm uppercase font-bold tracking-wider px-2.5 py-1 rounded-full {{ $transit->type === 'sun' ? 'bg-amber-950/60 text-amber-300 border border-amber-800' : 'bg-slate-950 text-blue-300 border border-slate-700' }}">
                            {{ $transit->type === 'sun' ? '☀️ Solar Transit' : '🌙 Lunar Transit' }}
                        </span>
                        @if($transit->is_exact_transit)
                        <span class="text-xs font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-green-900/70 text-green-300 border border-green-700">✓ Exact Transit</span>
                        @endif
                    </div>

                    <div>
                        <div class="text-slate-400 text-xs uppercase tracking-wider mb-0.5">Date &amp; Time</div>
                        <div class="text-white font-bold text-xl">{{ \Carbon\Carbon::parse($transit->time)->timezone(config('app.timezone', 'UTC'))->format('l, M jS') }}</div>
                        <div class="text-purple-300 font-extrabold text-2xl">{{ \Carbon\Carbon::parse($transit->time)->timezone(config('app.timezone', 'UTC'))->format('H:i:s') }}</div>
                    </div>

                    <div>
                        <div class="text-slate-400 text-xs uppercase tracking-wider mb-0.5">Location</div>
                        <div class="text-slate-200 font-semibold">{{ $transit->location->name }}</div>
                    </div>

                    @if($transit->type === 'moon')
                    @php $moon = $transit->getMoonPhase(); @endphp
                    <div>
                        <div class="text-slate-400 text-xs uppercase tracking-wider mb-0.5">Moon Phase</div>
                        <div class="text-slate-200 font-semibold flex items-center gap-1.5">
                            <span class="text-xl">{{ $moon['emoji'] }}</span>
                            <span>{{ $moon['name'] }}</span>
                            <span class="text-xs text-slate-500">({{ $moon['illumination'] }}% illuminated)</span>
                        </div>
                    </div>
                    @endif

                    <div class="pt-1">
                        <div class="text-slate-400 text-xs uppercase tracking-wider mb-1">Forecast Cloud Cover</div>
                        @if($transit->cloud_cover_percent !== null)
                            @if($transit->cloud_cover_percent >= 90)
                            <div class="px-3 py-2 rounded-xl bg-amber-950/70 border border-amber-600/40 text-amber-300 text-xs font-semibold flex items-center gap-2">
                                <svg class="w-4 h-4 shrink-0 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <div>
                                    <div class="font-bold">⚠️ Cloud Warning: {{ $transit->cloud_cover_percent }}% Overcast</div>
                                    <div class="text-[10px] text-amber-300/80 font-normal">Overcast skies predicted. Observation is unlikely to be possible.</div>
                                </div>
                            </div>
                            @elseif($transit->cloud_cover_percent < 30)
                            <div class="px-3 py-2 rounded-xl bg-emerald-950/50 border border-emerald-600/30 text-emerald-300 text-xs font-medium flex items-center gap-2">
                                <span>🌤️ Favorable Viewing: {{ $transit->cloud_cover_percent }}% Cloud Cover</span>
                            </div>
                            @else
                            <div class="px-3 py-2 rounded-xl bg-slate-800/60 border border-slate-700/50 text-slate-300 text-xs font-medium flex items-center gap-2">
                                <span>☁️ Partly Cloudy: {{ $transit->cloud_cover_percent }}% Cloud Cover</span>
                            </div>
                            @endif
                        @else
                            <div class="px-3 py-2 rounded-xl bg-slate-800/60 border border-slate-700/50 text-slate-400 text-xs font-medium flex items-center gap-2">
                                <span>☁️ Weather forecast pending next update</span>
                            </div>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-2 border-t border-slate-800">
                        <div class="bg-slate-800/60 rounded-xl p-3">
                            <div class="text-slate-500 text-[10px] uppercase tracking-wider">Separation</div>
                            <div class="text-{{ $transit->is_exact_transit ? 'green' : 'purple' }}-300 font-bold text-lg">{{ $transit->separation_degrees }}°</div>
                            <div class="text-slate-500 text-[10px]">{{ $transit->is_exact_transit ? '< disk radius' : '> disk radius' }}</div>
                        </div>
                        <div class="bg-slate-800/60 rounded-xl p-3">
                            <div class="text-slate-500 text-[10px] uppercase tracking-wider">Altitude</div>
                            <div class="text-slate-200 font-bold text-lg">{{ $transit->altitude_degrees }}°</div>
                            <div class="text-slate-500 text-[10px]">above horizon</div>
                        </div>
                        <div class="bg-slate-800/60 rounded-xl p-3">
                            <div class="text-slate-500 text-[10px] uppercase tracking-wider">Azimuth</div>
                            <div class="text-slate-200 font-bold text-lg">{{ $transit->azimuth_degrees }}°</div>
                            <div class="text-slate-500 text-[10px]">
                                @php
                                    $az = $transit->azimuth_degrees;
                                    $dirs = ['N','NE','E','SE','S','SW','W','NW','N'];
                                    echo $dirs[round($az / 45) % 8];
                                @endphp
                            </div>
                        </div>
                        <div class="bg-slate-800/60 rounded-xl p-3">
                            <div class="text-slate-500 text-[10px] uppercase tracking-wider">Path points</div>
                            <div class="text-slate-200 font-bold text-lg">{{ count($transit->path_points ?? []) }}</div>
                            <div class="text-slate-500 text-[10px]">samples recorded</div>
                        </div>
                    </div>

                    {{-- Modal Calendar & Community Share Actions --}}
                    @php
                        $mTimeIso = \Carbon\Carbon::parse($transit->time)->utc()->format('Ymd\THis\Z');
                        $mEndIso = \Carbon\Carbon::parse($transit->time)->addMinutes(15)->utc()->format('Ymd\THis\Z');
                        $mTypeStr = $transit->type === 'sun' ? 'Solar' : 'Lunar';
                        $mgTitle = urlencode("ISS {$mTypeStr} Transit — {$transit->location->name}");
                        $mgDesc = urlencode("ISS {$mTypeStr} Transit over {$transit->location->name}.\nSeparation: {$transit->separation_degrees}° (" . ($transit->is_exact_transit ? "True Transit" : "Conjunction") . ")\nAltitude: {$transit->altitude_degrees}°, Azimuth: {$transit->azimuth_degrees}°\nModeled via Astronotify: https://astronotify.org");
                        $mgLoc = urlencode("{$transit->location->name} ({$transit->location->latitude}, {$transit->location->longitude})");
                        $mgCalUrl = "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$mgTitle}&dates={$mTimeIso}/{$mEndIso}&details={$mgDesc}&location={$mgLoc}";
                        $msSummary = "ISS {$mTypeStr} Transit — {$transit->location->name}";
                        $msDateFormatted = \Carbon\Carbon::parse($transit->time)->timezone(config('app.timezone', 'UTC'))->format('l, M jS \a\t H:i:s');
                        $msCloudStr = $transit->cloud_cover_percent !== null ? $transit->cloud_cover_percent . "% cloud" : "Pending";
                        $msText = "🛰️ ISS {$mTypeStr} Transit over {$transit->location->name}\n📅 {$msDateFormatted}\n🎯 Separation: {$transit->separation_degrees}° (" . ($transit->is_exact_transit ? "True Transit" : "Conjunction") . ") | Alt: {$transit->altitude_degrees}°\n☁️ Forecast: {$msCloudStr}\nModeled via Astronotify: https://astronotify.org";
                        $msWhatsApp = "https://api.whatsapp.com/send?text=" . urlencode($msText);
                    @endphp
                    <div class="pt-3 border-t border-slate-800 space-y-2">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Add to Calendar &amp; Share with Club</div>
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ $mgCalUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-950/70 hover:bg-blue-900 border border-blue-500/40 text-blue-200 text-xs font-semibold transition-all">
                                <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>Google Calendar</span>
                            </a>
                            <button type="button" @click="downloadIcs(@js($msSummary), @js($transit->location->name), @js($msText), @js($mTimeIso), @js($mEndIso))" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-purple-950/70 hover:bg-purple-900 border border-purple-500/40 text-purple-200 text-xs font-semibold transition-all">
                                <svg class="w-3.5 h-3.5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                <span>iCal (.ics)</span>
                            </button>
                            <button type="button" @click="shareTransit(@js($msSummary), @js($msText))" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-950/70 hover:bg-emerald-900 border border-emerald-500/40 text-emerald-200 text-xs font-semibold transition-all">
                                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                                <span>Copy / Share Pass</span>
                            </button>
                            <a href="{{ $msWhatsApp }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-green-950/70 hover:bg-green-900 border border-green-500/40 text-green-200 text-xs font-semibold transition-all">
                                <span>💬 WhatsApp</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-[10px] text-slate-600 leading-relaxed">
                    <span class="inline-block w-3 h-0.5 bg-purple-500 mr-1 align-middle"></span> ISS trajectory &nbsp;
                    <span class="inline-block w-2 h-2 rounded-full bg-purple-300 mr-1 align-middle"></span> Closest approach &nbsp;
                    @if($transit->is_exact_transit)
                    <span class="text-green-600">Path crosses the disk — a true transit!</span>
                    @else
                    <span class="text-slate-600">Near miss — path clears the disk edge</span>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Toast notification when copying transit details --}}
    <div 
        x-show="shareCopied" 
        x-transition:enter="transition ease-out duration-200" 
        x-transition:enter-start="opacity-0 translate-y-2" 
        x-transition:enter-end="opacity-100 translate-y-0" 
        x-transition:leave="transition ease-in duration-150" 
        x-transition:leave-start="opacity-100 translate-y-0" 
        x-transition:leave-end="opacity-0 translate-y-2" 
        style="display: none;"
        class="fixed bottom-6 right-6 z-50 px-4 py-2.5 rounded-2xl bg-emerald-950 border border-emerald-500/50 text-emerald-200 text-xs font-bold shadow-2xl flex items-center gap-2"
    >
        <span>✓</span>
        <span>Transit details copied! Ready to paste into WhatsApp, Discord, or forums.</span>
    </div>

</div>
