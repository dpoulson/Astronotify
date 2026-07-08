<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-gray-200 leading-tight">
            {{ __('About Astronotify') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-slate-950 flex-grow text-white">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <div class="bg-slate-800/40 backdrop-blur-xl border border-slate-700/50 rounded-3xl p-8 shadow-2xl relative overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-purple-500/5 rounded-full blur-3xl"></div>
                
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
                            <strong class="text-slate-200 block">Max Wind and Clouds</strong>
                            <span class="text-xs text-slate-400">Wind speeds over 20 km/h cause telescope vibrations (affecting high-power observing), while cloud cover over 20% blocks targets. Adjust these based on your equipment and local microclimate.</span>
                        </div>
                        <div class="border-l-4 border-yellow-500 pl-4 py-1">
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
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
