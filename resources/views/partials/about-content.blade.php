<div class="bg-slate-800/40 backdrop-blur-xl border border-slate-700/50 rounded-3xl p-8 shadow-2xl relative overflow-hidden">
    <div class="absolute top-0 right-0 w-64 h-64 bg-purple-500/5 rounded-full blur-3xl pointer-events-none"></div>
    
    <h3 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-500 mb-6">
        What is Astronotify?
    </h3>
    
    <div class="prose prose-invert max-w-none text-slate-300 space-y-6">
        <p class="text-lg leading-relaxed">
            Astronotify is your automated personal stargazing assistant. We continuously monitor local weather conditions and satellite orbital trajectories so you never miss a clear night or a rare celestial alignment.
        </p>

        <h4 class="text-xl font-bold text-slate-200 mt-8">Core Features</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
            <div class="bg-slate-900/60 p-5 rounded-2xl border border-slate-800 space-y-2">
                <span class="text-2xl">🌤️</span>
                <h5 class="text-base font-bold text-white">Optimal Weather Alerts</h5>
                <p class="text-xs text-slate-400">Specify your tolerances for clouds, wind speed, and night duration. Astronotify fetches forecasts daily and emails you when your exact stargazing window is met.</p>
            </div>
            <div class="bg-slate-900/60 p-5 rounded-2xl border border-slate-800 space-y-2">
                <span class="text-2xl">🛰️</span>
                <h5 class="text-base font-bold text-white">ISS Transit Modeling</h5>
                <p class="text-xs text-slate-400">Uses high-precision SGP4 orbital propagation to calculate when the ISS crosses the face of the Sun or Moon. Displays interactive path visualizations and separation details.</p>
            </div>
            <div class="bg-slate-900/60 p-5 rounded-2xl border border-slate-800 space-y-2">
                <span class="text-2xl">🌑</span>
                <h5 class="text-base font-bold text-white">Moon Phase Tracking</h5>
                <p class="text-xs text-slate-400">Computes the exact lunar phase and illumination fraction. Highlights whether a dark sky window is optimal for faint deep-sky targets, or if planetary observing is preferred.</p>
            </div>
            <div class="bg-slate-900/60 p-5 rounded-2xl border border-slate-800 space-y-2">
                <span class="text-2xl">🌌</span>
                <h5 class="text-base font-bold text-white">Light Pollution Insights</h5>
                <p class="text-xs text-slate-400">Records Bortle scale light pollution ratings from Class 1 (excellent dark skies) to Class 9 (city centers). Links directly to geographical light pollution maps.</p>
            </div>
        </div>

        <h4 class="text-xl font-bold text-slate-200 mt-10">Guide to Location Parameters</h4>
        <p class="text-sm text-slate-400 leading-relaxed">
            To get the most accurate predictions, it is important to configure each location's stargazing parameters:
        </p>

        <div class="space-y-4">
            <div class="border-l-4 border-purple-500 pl-4 py-1">
                <strong class="text-slate-200 block">Coordinates & Elevation</strong>
                <span class="text-xs text-slate-400">Latitude and longitude pinpoint your observing site. Elevation is critical because parallax shifting (especially for low-altitude passes) changes the perceived path of the ISS across the moon by up to 0.5 degrees.</span>
            </div>
            <div class="border-l-4 border-blue-500 pl-4 py-1">
                <strong class="text-slate-200 block">Minimum Night Duration</strong>
                <span class="text-xs text-slate-400">The minimum length of the dark night (sunset to sunrise) required to make stargazing worthwhile. Useful during summer months to filter out short nights where twilight remains bright.</span>
            </div>
            <div class="border-l-4 border-teal-500 pl-4 py-1">
                <strong class="text-slate-200 block">Minimum Clear Hours</strong>
                <span class="text-xs text-slate-400">The number of continuous, uninterrupted clear hours required during the night to trigger an alert.</span>
            </div>
            <div class="border-l-4 border-amber-500 pl-4 py-1">
                <strong class="text-slate-200 block">Maximum Cloud & Wind Limits</strong>
                <span class="text-xs text-slate-400">Set upper limits on cloud cover percentage and wind speed (in km/h). Higher winds cause telescope mount vibration and blur long exposures.</span>
            </div>
            <div class="border-l-4 border-pink-500 pl-4 py-1">
                <strong class="text-slate-200 block">Bortle Scale</strong>
                <span class="text-xs text-slate-400">A numerical scale representing the brightness of the night sky:
                    <ul class="list-disc pl-5 mt-1 space-y-1 text-[11px]">
                        <li><strong>Class 1-3:</strong> Rural / Pristine Dark Skies (excellent contrast, visible Milky Way details).</li>
                        <li><strong>Class 4-6:</strong> Suburban Skies (moderate light dome, Milky Way is weak or invisible near horizon).</li>
                        <li><strong>Class 7-9:</strong> Urban/City Skies (highly light polluted, only planets, moon, and bright double stars are observable).</li>
                    </ul>
                </span>
            </div>
        </div>

        <!-- Supporting Astronotify -->
        <div class="mt-8 rounded-2xl bg-gradient-to-r from-purple-950/40 via-slate-900/80 to-slate-900/60 border border-purple-500/30 p-6 sm:p-7 relative overflow-hidden shadow-xl">
            <div class="absolute -right-8 -top-8 w-44 h-44 bg-purple-500/10 rounded-full blur-2xl pointer-events-none"></div>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 relative z-10">
                <div class="space-y-2 max-w-xl">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-purple-900/50 border border-purple-700/50 text-purple-300 text-xs font-semibold">
                        <svg class="w-3.5 h-3.5 text-rose-400 fill-current" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                        <span>Independent &amp; Free</span>
                    </div>
                    <h4 class="text-xl font-bold text-white tracking-tight">Support Astronotify</h4>
                    <p class="text-sm text-slate-300 leading-relaxed">
                        Astronotify is completely free, ad-free, and open to the stargazing community. Orbital mechanics calculations, satellite ephemerides, and daily meteorological predictions run on dedicated cloud servers funded out of pocket. If Astronotify has helped you plan observing nights or capture ISS transits, a voluntary donation helps keep the project going.
                    </p>
                </div>
                <div class="shrink-0 flex flex-col items-start sm:items-end gap-2">
                    <x-donate-button>Support via PayPal</x-donate-button>
                    <span class="text-[11px] text-slate-400">One-off contribution &bull; Every bit helps!</span>
                </div>
            </div>
        </div>

        <div class="pt-6 border-t border-slate-800 flex items-center justify-between">
            <span class="text-xs text-slate-500">Need more technical details?</span>
            <a href="{{ route('faq') }}" class="text-xs font-bold text-purple-400 hover:text-purple-300 flex items-center gap-1">
                <span>View Help &amp; Frequently Asked Questions</span>
                <span>&rarr;</span>
            </a>
        </div>
    </div>
</div>
