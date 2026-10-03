<x-slot name="title">{{ $transitTitle }} — Astronotify</x-slot>

@push('meta')
    <meta name="description" content="{{ $transitTitle }} on {{ $timeFormatted }}. Separation: {{ $transit->separation_degrees }}°. Modeled with SGP4 precision via Astronotify.">
    <meta property="og:title" content="{{ $transitTitle }}">
    <meta property="og:description" content="Occurring on {{ $timeFormatted }}. Separation: {{ $transit->separation_degrees }}° ({{ $transit->is_exact_transit ? 'True Transit' : 'Conjunction' }}). Modeled with SGP4 precision via Astronotify.">
    <meta property="og:type" content="event">
    <meta property="og:url" content="{{ url('/transit/' . $transit->public_token) }}">
    <meta property="og:image" content="{{ asset('images/og-card.png') }}">
    <link rel="canonical" href="{{ url('/transit/' . $transit->public_token) }}">

    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "Event",
        "name": "{{ $transitTitle }}",
        "startDate": "{{ $transit->time->toIso8601String() }}",
        "eventStatus": "https://schema.org/EventScheduled",
        "eventAttendanceMode": "https://schema.org/OfflineEventAttendanceMode",
        "location": {
            "@@type": "Place",
            "name": "{{ $location ? $location->name : 'Observing Spot' }}",
            "geo": {
                "@@type": "GeoCoordinates",
                "latitude": {{ $location ? $location->latitude : 0 }},
                "longitude": {{ $location ? $location->longitude : 0 }}
            }
        },
        "description": "High-precision SGP4 modeled orbital pass of the International Space Station crossing the {{ $transit->type === 'sun' ? 'Sun' : 'Moon' }}."
    }
    </script>
@endpush

<div 
    x-data="{
        copied: false,
        copyLink() {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(window.location.href);
                this.copied = true;
                setTimeout(() => { this.copied = false; }, 3000);
            }
        },
        downloadIcs() {
            const summary = @js($transitTitle);
            const location = @js($location ? $location->name : 'Observing Spot');
            const description = @js($transitDescription);
            const startTimeUtc = @js($timeIso);
            const endTimeUtc = @js($endIso);

            const icsLines = [
                'BEGIN:VCALENDAR',
                'VERSION:2.0',
                'PRODID:-//Astronotify//Astronomical Transit Calendar//EN',
                'CALSCALE:GREGORIAN',
                'METHOD:PUBLISH',
                'BEGIN:VEVENT',
                `UID:transit-${@js($transit->id)}@astronotify.org`,
                `DTSTAMP:${startTimeUtc}`,
                `DTSTART:${startTimeUtc}`,
                `DTEND:${endTimeUtc}`,
                `SUMMARY:${summary}`,
                `DESCRIPTION:${description.replace(/\n/g, '\\n')}`,
                `LOCATION:${location}`,
                'BEGIN:VALARM',
                'TRIGGER:-PT15M',
                'ACTION:DISPLAY',
                'DESCRIPTION:Reminder: ISS Transit in 15 minutes!',
                'END:VALARM',
                'END:VEVENT',
                'END:VCALENDAR'
            ];
            const blob = new Blob([icsLines.join('\r\n')], { type: 'text/calendar;charset=utf-8' });
            const link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);
            link.setAttribute('download', `iss-transit-${startTimeUtc}.ics`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    }"
    class="min-h-screen bg-slate-950 text-white selection:bg-purple-500 selection:text-white"
>
    <!-- Background Accents -->
    <div class="relative overflow-hidden pt-12 pb-16 border-b border-slate-800">
        <div class="absolute inset-0 pointer-events-none opacity-40">
            <div class="absolute top-[-15%] left-[10%] w-[50%] h-[50%] bg-purple-900/30 rounded-full blur-[150px]"></div>
            <div class="absolute bottom-[-10%] right-[10%] w-[50%] h-[50%] bg-pink-900/20 rounded-full blur-[160px]"></div>
        </div>

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <!-- Breadcrumbs -->
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-6 font-medium">
                <a href="{{ url('/') }}" class="hover:text-purple-400 transition">Home</a>
                <span>/</span>
                <a href="{{ route('spots.index') }}" class="hover:text-purple-400 transition">Passes</a>
                <span>/</span>
                <span class="text-slate-200">Transit Prediction</span>
            </div>

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-purple-950/80 border border-purple-500/40 text-purple-300 text-xs font-bold uppercase tracking-wider mb-3">
                        <span>🛰️</span>
                        <span>SGP4 Orbital Modeling</span>
                    </div>

                    <h1 class="text-3xl sm:text-5xl font-black tracking-tight text-white">
                        {{ $transitTitle }}
                    </h1>
                    <p class="mt-2 text-slate-300 text-base sm:text-lg">
                        {{ $timeFormatted }}
                    </p>
                    <p class="text-xs text-slate-500 font-mono mt-1">
                        {{ $timeUtc }}
                    </p>
                </div>

                {{-- Status / Separation Badge --}}
                <div class="shrink-0 text-left md:text-right">
                    <span class="inline-block px-4 py-2 rounded-2xl text-sm font-black border {{ $transit->is_exact_transit ? 'bg-pink-950/80 text-pink-300 border-pink-500/50 shadow-lg shadow-pink-950/40' : 'bg-purple-950/80 text-purple-300 border-purple-500/50' }}">
                        {{ $transit->is_exact_transit ? '🎯 True Disc Transit' : '⚡ Close Conjunction' }}
                    </span>
                    <div class="text-xs text-slate-400 mt-1 font-mono">
                        Separation: <strong class="text-white">{{ (float) $transit->separation_degrees }}°</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-12">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
            <!-- Visual Celestial Diagram -->
            <div class="p-8 rounded-3xl bg-slate-900/80 border border-slate-800 shadow-2xl flex flex-col items-center justify-center relative overflow-hidden min-h-[380px]">
                <div class="absolute top-3 left-4 text-xs font-bold uppercase tracking-wider text-slate-400">
                    Modeled Pass Chord
                </div>

                @if($transit->type === 'sun')
                    <!-- Sun Representation -->
                    <div class="relative w-64 h-64 rounded-full bg-gradient-to-tr from-amber-500 via-yellow-400 to-amber-200 shadow-[0_0_80px_rgba(245,158,11,0.5)] flex items-center justify-center overflow-hidden my-6">
                        <!-- Sunspots -->
                        <div class="absolute top-16 left-20 w-3 h-2 rounded-full bg-amber-900/60 blur-[0.5px]"></div>
                        <div class="absolute bottom-20 right-24 w-4 h-3 rounded-full bg-amber-950/70 blur-[0.5px]"></div>
                        <div class="absolute top-28 right-16 w-2 h-2 rounded-full bg-amber-900/50"></div>

                        <!-- Transit Vector -->
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="w-80 h-1 bg-gradient-to-r from-transparent via-purple-600 to-transparent -rotate-12 shadow-[0_0_12px_#a855f7]"></div>
                            <div class="absolute w-4 h-4 rounded-full bg-pink-500 border-2 border-white shadow-[0_0_15px_#ec4899] animate-pulse"></div>
                        </div>
                    </div>
                @else
                    <!-- Moon Representation -->
                    <div class="relative w-64 h-64 rounded-full bg-slate-300 shadow-[0_0_70px_rgba(168,85,247,0.35)] flex items-center justify-center overflow-hidden my-6">
                        <!-- Craters -->
                        <div class="absolute top-12 left-14 w-8 h-8 rounded-full bg-slate-400/50"></div>
                        <div class="absolute bottom-16 right-16 w-12 h-12 rounded-full bg-slate-400/60"></div>
                        <div class="absolute top-24 right-20 w-6 h-6 rounded-full bg-slate-400/40"></div>
                        <div class="absolute bottom-28 left-20 w-7 h-7 rounded-full bg-slate-400/45"></div>

                        <!-- Shadow overlay -->
                        <div class="absolute -top-4 -left-12 w-64 h-72 rounded-full bg-slate-950/85"></div>

                        <!-- Transit Vector -->
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="w-80 h-1 bg-gradient-to-r from-transparent via-pink-500 to-transparent -rotate-35 shadow-[0_0_12px_#ec4899]"></div>
                            <div class="absolute w-4 h-4 rounded-full bg-pink-400 border-2 border-white shadow-[0_0_15px_#f472b6] animate-pulse"></div>
                        </div>
                    </div>
                @endif

                <div class="text-center mt-2">
                    <span class="inline-block px-3 py-1 rounded-full bg-slate-950/90 border border-slate-700 text-xs font-mono text-slate-300">
                        {{ $transit->type === 'sun' ? 'Solar Disc Diameter ~0.5°' : ($moonPhase['name'] ?? 'Lunar Disc') . ' (~' . ($moonPhase['illumination'] ?? 50) . '% lit)' }}
                    </span>
                </div>
            </div>

            <!-- Pass Telemetry & Metrics -->
            <div class="space-y-6">
                <div class="p-6 rounded-3xl bg-slate-900/80 border border-slate-800 shadow-xl space-y-4">
                    <h2 class="text-base font-bold text-white flex items-center gap-2">
                        <span>🧭</span> Observation Telemetry
                    </h2>

                    <div class="space-y-2.5 text-xs">
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-950/70 border border-slate-800">
                            <span class="text-slate-400">Target Body:</span>
                            <span class="font-bold text-white uppercase">{{ $transit->type === 'sun' ? 'Sun (Solar Pass)' : 'Moon (Lunar Pass)' }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-950/70 border border-slate-800">
                            <span class="text-slate-400">Closest Approach Separation:</span>
                            <span class="font-bold text-pink-400 font-mono">{{ (float) $transit->separation_degrees }}°</span>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-950/70 border border-slate-800">
                            <span class="text-slate-400">Altitude Above Horizon:</span>
                            <span class="font-bold text-slate-200 font-mono">{{ (float) $transit->altitude_degrees }}°</span>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-950/70 border border-slate-800">
                            <span class="text-slate-400">Azimuth (Compass Heading):</span>
                            <span class="font-bold text-slate-200 font-mono">{{ (float) $transit->azimuth_degrees }}°</span>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-950/70 border border-slate-800">
                            <span class="text-slate-400">Cloud Cover Forecast:</span>
                            <span class="font-bold font-mono {{ $transit->cloud_cover_percent !== null && $transit->cloud_cover_percent >= 90 ? 'text-rose-400' : 'text-emerald-400' }}">
                                {{ $transit->cloud_cover_percent !== null ? $transit->cloud_cover_percent . '%' : 'Forecast pending' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Calendar Export & Share Actions -->
                <div class="p-6 rounded-3xl bg-slate-900/80 border border-slate-800 shadow-xl space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        Export to Calendar &amp; Share
                    </h3>

                    <div class="grid grid-cols-2 gap-3">
                        <a 
                            href="{{ $gCalUrl }}" 
                            target="_blank" 
                            rel="noopener noreferrer" 
                            class="px-4 py-2.5 rounded-xl bg-blue-950/80 hover:bg-blue-900 border border-blue-500/40 text-blue-200 text-xs font-bold flex items-center justify-center gap-2 transition"
                        >
                            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>Google Calendar</span>
                        </a>

                        <button 
                            type="button" 
                            @click="downloadIcs()" 
                            class="px-4 py-2.5 rounded-xl bg-purple-950/80 hover:bg-purple-900 border border-purple-500/40 text-purple-200 text-xs font-bold flex items-center justify-center gap-2 transition"
                        >
                            <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Download iCal (.ics)</span>
                        </button>
                    </div>

                    <div class="flex items-center gap-2 pt-2 border-t border-slate-800">
                        <a 
                            href="{{ $whatsAppUrl }}" 
                            target="_blank" 
                            rel="noopener noreferrer" 
                            class="flex-1 py-2 rounded-xl bg-emerald-950/80 hover:bg-emerald-900 border border-emerald-500/40 text-emerald-200 text-xs font-semibold text-center transition"
                        >
                            WhatsApp
                        </a>
                        <a 
                            href="{{ $twitterUrl }}" 
                            target="_blank" 
                            rel="noopener noreferrer" 
                            class="flex-1 py-2 rounded-xl bg-sky-950/80 hover:bg-sky-900 border border-sky-500/40 text-sky-200 text-xs font-semibold text-center transition"
                        >
                            Share on X
                        </a>
                        <button 
                            type="button" 
                            @click="copyLink()" 
                            class="flex-1 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold text-center transition"
                        >
                            <span x-show="!copied">Copy Link</span>
                            <span x-show="copied" class="text-emerald-400">Copied!</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Conversion Callout for Observers & Astrophotographers -->
        <div class="p-8 rounded-3xl bg-gradient-to-r from-purple-950/60 via-slate-900 to-pink-950/60 border border-purple-500/30 shadow-2xl flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="max-w-2xl">
                <h3 class="text-2xl font-bold text-white">Capture the International Space Station over your house</h3>
                <p class="text-sm text-slate-300 mt-2">
                    Never miss an ISS solar or lunar transit chord again. Add your telescope observing spots, set your cloud thresholds, and get automated email notifications when a pass is imminent. Zero tracking, 100% free forever.
                </p>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ route('register') }}" class="px-6 py-3.5 rounded-2xl bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-white font-bold text-sm shadow-xl transition active:scale-95">
                    Start Stargazing Free &rarr;
                </a>
            </div>
        </div>
    </div>

    @include('components.footer')
</div>
