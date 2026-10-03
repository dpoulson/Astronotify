<div class="space-y-8" x-data="{ activeTab: 'transits', search: '' }">
    <!-- Header Hero Banner -->
    <div class="bg-slate-800/40 backdrop-blur-xl border border-slate-700/50 rounded-3xl p-8 shadow-2xl relative overflow-hidden">
        <div class="absolute -top-12 -right-12 w-64 h-64 bg-purple-600/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-12 -left-12 w-64 h-64 bg-blue-600/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10">
            <span class="text-xs uppercase font-extrabold tracking-widest text-purple-400 bg-purple-950/60 border border-purple-800/60 px-3 py-1 rounded-full inline-block mb-3">Knowledge Base</span>
            <h1 class="text-3xl sm:text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 via-purple-300 to-amber-300 mb-3">
                Help &amp; Frequently Asked Questions
            </h1>
            <p class="text-slate-300 text-sm sm:text-base max-w-3xl leading-relaxed">
                Learn how Astronotify models celestial transits, computes orbital mechanics, evaluates meteorological conditions, and notifies you when observing conditions are optimal.
            </p>
        </div>
    </div>

    <!-- Solar Filter Safety Alert Banner -->
    <div class="bg-amber-950/60 border border-amber-600/50 rounded-2xl p-5 text-amber-200 shadow-xl flex items-start gap-4">
        <div class="text-amber-400 p-2 rounded-xl bg-amber-900/50 border border-amber-700/50 shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <div class="space-y-1">
            <h4 class="font-extrabold text-white text-base flex items-center gap-2">
                <span>Critical Safety Warning for Solar Transits</span>
                <span class="text-xs font-semibold px-2 py-0.5 rounded bg-red-900/80 text-red-200 border border-red-700">Permanent Eye Injury Risk</span>
            </h4>
            <p class="text-xs sm:text-sm text-amber-200/90 leading-relaxed">
                <strong>NEVER</strong> look directly at the Sun or observe a solar transit with binoculars, cameras, or telescopes without a certified, securely mounted solar filter (such as Baader AstroSolar Safety Film or a dedicated Hydrogen-Alpha/white-light solar telescope). Focusing unfiltered solar rays onto your retina causes instant, irreversible blindness and can permanently damage camera sensors.
            </p>
        </div>
    </div>

    <!-- Quick Navigation Pills -->
    <div class="flex flex-wrap gap-2 border-b border-slate-800 pb-3">
        <button
            type="button"
            @click="activeTab = 'transits'"
            :class="activeTab === 'transits' ? 'bg-purple-600 text-white font-bold' : 'bg-slate-900/80 text-slate-400 hover:text-white border border-slate-800'"
            class="px-4 py-2 rounded-xl text-xs sm:text-sm transition-all flex items-center gap-2"
        >
            <span>🛰️</span>
            <span>ISS Transits &amp; Geometry</span>
        </button>
        <button
            type="button"
            @click="activeTab = 'weather'"
            :class="activeTab === 'weather' ? 'bg-purple-600 text-white font-bold' : 'bg-slate-900/80 text-slate-400 hover:text-white border border-slate-800'"
            class="px-4 py-2 rounded-xl text-xs sm:text-sm transition-all flex items-center gap-2"
        >
            <span>🌤️</span>
            <span>Weather &amp; Thresholds</span>
        </button>
        <button
            type="button"
            @click="activeTab = 'astronomy'"
            :class="activeTab === 'astronomy' ? 'bg-purple-600 text-white font-bold' : 'bg-slate-900/80 text-slate-400 hover:text-white border border-slate-800'"
            class="px-4 py-2 rounded-xl text-xs sm:text-sm transition-all flex items-center gap-2"
        >
            <span>🌑</span>
            <span>Moon Phases &amp; Bortle Scale</span>
        </button>
        <button
            type="button"
            @click="activeTab = 'alerts'"
            :class="activeTab === 'alerts' ? 'bg-purple-600 text-white font-bold' : 'bg-slate-900/80 text-slate-400 hover:text-white border border-slate-800'"
            class="px-4 py-2 rounded-xl text-xs sm:text-sm transition-all flex items-center gap-2"
        >
            <span>🔔</span>
            <span>Alerts &amp; Deduplication</span>
        </button>
        <button
            type="button"
            @click="activeTab = 'privacy'"
            :class="activeTab === 'privacy' ? 'bg-purple-600 text-white font-bold' : 'bg-slate-900/80 text-slate-400 hover:text-white border border-slate-800'"
            class="px-4 py-2 rounded-xl text-xs sm:text-sm transition-all flex items-center gap-2"
        >
            <span>🛡️</span>
            <span>Privacy &amp; Data Control</span>
        </button>
    </div>

    <!-- TAB 1: ISS Transits & Geometry -->
    <div x-show="activeTab === 'transits'" class="space-y-6">
        <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-6 space-y-4">
            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                <span class="text-purple-400">#</span>
                What is an ISS Transit vs. a Close Conjunction?
            </h3>
            <div class="text-sm text-slate-300 space-y-3 leading-relaxed">
                <p>
                    The International Space Station (ISS) orbits Earth approximately every 93 minutes at an altitude of ~400 km and a speed of ~27,600 km/h (7.66 km/s).
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <div class="bg-slate-950/70 border border-slate-800 rounded-xl p-4">
                        <div class="text-xs uppercase font-extrabold text-green-400 tracking-wider mb-1 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-green-400"></span> Exact Transit (&le; 0.26&deg;)
                        </div>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            The space station passes physically between your location and the surface of the Sun or Moon. The silhouette crosses the face in <strong>0.5 to 1.5 seconds</strong>. High shutter speed photography (&ge; 1/2000s) or video is recommended.
                        </p>
                    </div>
                    <div class="bg-slate-950/70 border border-slate-800 rounded-xl p-4">
                        <div class="text-xs uppercase font-extrabold text-blue-400 tracking-wider mb-1 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-blue-400"></span> Close Conjunction (0.26&deg; to 0.75&deg;)
                        </div>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            The ISS does not cross the disc itself, but passes close to the limb or through the solar corona. Excellent for viewing the flyby alongside a crescent moon or wide-field astrophotography.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-6 space-y-4">
            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                <span class="text-purple-400">#</span>
                What does "Separation Degrees" mean?
            </h3>
            <div class="text-sm text-slate-300 space-y-3 leading-relaxed">
                <p>
                    As viewed from the surface of Earth, both the Sun and the Moon subtend an apparent angular diameter of approximately <strong>0.53&deg; (approx. half a degree)</strong> in the sky.
                </p>
                <p>
                    Because the angular radius from the center to the edge (limb) is <strong>~0.26&deg;</strong>, the separation distance reported on your dashboard is the angular distance between the <strong>exact center</strong> of the Sun or Moon and the closest point of the ISS trajectory:
                </p>
                <ul class="list-disc list-inside space-y-1.5 text-xs text-slate-300 pl-2">
                    <li><strong>0.00&deg;</strong>: Direct bullseye transit across the center of the celestial body.</li>
                    <li><strong>0.01&deg; &ndash; 0.26&deg;</strong>: Transit crossing the surface disc.</li>
                    <li><strong>0.27&deg; &ndash; 0.75&deg;</strong>: Close conjunction skimming past the outer limb.</li>
                    <li><strong>&gt; 0.75&deg;</strong>: Wide pass, not considered a conjunction.</li>
                </ul>
            </div>
        </div>

        <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-6 space-y-4">
            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                <span class="text-purple-400">#</span>
                Why are Transit Paths so Narrow?
            </h3>
            <div class="text-sm text-slate-300 space-y-2 leading-relaxed">
                <p>
                    Because the ISS is only 400 km away while the Sun is 150 million km away and the Moon is 384,400 km away, parallax is extreme. The ground corridor where an exact transit is visible is typically only <strong>1 to 3 kilometers wide</strong>.
                </p>
                <p class="text-xs text-slate-400">
                    Moving just 500 meters or 1 kilometer north or south of the centerline can transform an exact transit into a grazing conjunction or complete miss. Always ensure your location's GPS coordinates are exact.
                </p>
            </div>
        </div>
    </div>

    <!-- TAB 2: Weather & Thresholds -->
    <div x-show="activeTab === 'weather'" class="space-y-6">
        <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-6 space-y-4">
            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                <span class="text-purple-400">#</span>
                How to Configure Viewing Thresholds for Your Gear
            </h3>
            <div class="space-y-4 text-sm text-slate-300">
                <div class="border-l-4 border-purple-500 pl-4 py-1">
                    <strong class="text-white block font-semibold">Maximum Cloud Cover (&percnt;)</strong>
                    <span class="text-xs text-slate-400">The maximum permissible cloud percentage during clear hours. For deep-sky imaging (DSO), set this to 10%&ndash;20%. For planetary or lunar viewing, 30%&ndash;40% is often acceptable since planets can be observed between passing cloud banks.</span>
                </div>

                <div class="border-l-4 border-blue-500 pl-4 py-1">
                    <strong class="text-white block font-semibold">Minimum Clear Window (Hours)</strong>
                    <span class="text-xs text-slate-400">The minimum continuous stretch of clear skies required in a night. Prevents false alarms when skies only clear for a 30-minute transient window between storms.</span>
                </div>

                <div class="border-l-4 border-emerald-500 pl-4 py-1">
                    <strong class="text-white block font-semibold">Maximum Wind Speed (km/h)</strong>
                    <span class="text-xs text-slate-400">Telescopes acting like sails on equatorial mounts vibrate in wind gusts, ruining long exposures. Typical setups perform best under 15&ndash;20 km/h (10&ndash;12 mph).</span>
                </div>

                <div class="border-l-4 border-amber-500 pl-4 py-1">
                    <strong class="text-white block font-semibold">Minimum Night Duration (Hours)</strong>
                    <span class="text-xs text-slate-400">The total dark hours from sunset to sunrise. In mid-summer at latitudes above 50&deg;N (e.g. UK, Northern Europe), astronomical twilight never completely ends and nights are very short. Setting this to 4&ndash;6 hours filters out light summer nights.</span>
                </div>

                <div class="border-l-4 border-indigo-500 pl-4 py-1">
                    <strong class="text-white block font-semibold">Elevation (Meters Above Sea Level)</strong>
                    <span class="text-xs text-slate-400">Elevation directly impacts the observer's apparent horizon and parallax angles. Entering accurate elevation ensures that satellite pass altitudes and transit chords remain millimeter-accurate.</span>
                </div>
            </div>
        </div>

        <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-6 space-y-4">
            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                <span class="text-purple-400">#</span>
                Where does weather data come from?
            </h3>
            <p class="text-sm text-slate-300 leading-relaxed">
                Astronotify integrates directly with the <strong>Open-Meteo</strong> meteorological API, pulling high-resolution numerical weather prediction models (including ECMWF, GFS, and DWD ICON) aggregated every day at midnight for your exact coordinates.
            </p>
        </div>
    </div>

    <!-- TAB 3: Moon Phases & Bortle Scale -->
    <div x-show="activeTab === 'astronomy'" class="space-y-6">
        <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-6 space-y-4">
            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                <span class="text-purple-400">#</span>
                Understanding the Bortle Dark-Sky Scale
            </h3>
            <p class="text-sm text-slate-300 leading-relaxed">
                The Bortle scale measures the night sky's brightness and light pollution on a scale of 1 to 9:
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2 text-xs">
                <div class="bg-slate-950 p-4 rounded-xl border border-slate-800">
                    <div class="font-bold text-emerald-400 mb-1">Class 1 &ndash; 3: Dark Sky</div>
                    <p class="text-slate-400">Rural and pristine dark sky reserves. Milky Way casts diffuse shadows on the ground; zodiacal light and faint deep-sky targets easily visible.</p>
                </div>
                <div class="bg-slate-950 p-4 rounded-xl border border-slate-800">
                    <div class="font-bold text-yellow-400 mb-1">Class 4 &ndash; 6: Suburban</div>
                    <p class="text-slate-400">Suburban transition skies. Milky Way visible overhead in summer, light domes present on horizons. Good for planetary, lunar, and bright nebulae.</p>
                </div>
                <div class="bg-slate-950 p-4 rounded-xl border border-slate-800">
                    <div class="font-bold text-red-400 mb-1">Class 7 &ndash; 9: Urban / City</div>
                    <p class="text-slate-400">City centers and inner suburbs. Sky glows orange or greyish white. Limited to the Moon, major planets, and bright double stars.</p>
                </div>
            </div>
            <p class="text-xs text-slate-400 pt-2">
                Tip: You can look up your location's exact Bortle rating using the interactive light pollution map linked directly from your location cards.
            </p>
        </div>

        <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-6 space-y-4">
            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                <span class="text-purple-400">#</span>
                How Moon Illumination Impacts Observing
            </h3>
            <p class="text-sm text-slate-300 leading-relaxed">
                Astronotify automatically calculates the lunar phase and illumination fraction for each forecast date:
            </p>
            <ul class="list-disc list-inside space-y-1 text-xs text-slate-300 pl-2">
                <li><strong>New Moon / Crescent (&lt; 25% illumination)</strong>: Ideal for deep-sky imaging, galaxy hunting, and meteor showers.</li>
                <li><strong>Gibbous / Full Moon (&gt; 75% illumination)</strong>: Heavy lunar skyglow washes out faint objects. Best utilized for high-magnification lunar crater observing or planetary viewing.</li>
            </ul>
        </div>
    </div>

    <!-- TAB 4: Alerts & Deduplication -->
    <div x-show="activeTab === 'alerts'" class="space-y-6">
        <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-6 space-y-4">
            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                <span class="text-purple-400">#</span>
                How Alert Deduplication &amp; Weather Gating Work
            </h3>
            <div class="text-sm text-slate-300 space-y-3 leading-relaxed">
                <p>
                    Nobody likes inbox clutter. Astronotify uses intelligent state tracking so you are only notified when real, actionable observing opportunities arise:
                </p>
                <div class="space-y-3 pt-2 text-xs">
                    <div class="bg-slate-950/70 p-3.5 rounded-xl border border-slate-800">
                        <strong class="text-purple-300 block mb-1">🌤️ Weather Alerts (No Re-Alerting on Fluctuation)</strong>
                        <p class="text-slate-400">Each calendar night is timestamped upon notification. If a forecast's cloud cover fluctuates back and forth over several days, you will not receive repeat notifications for that same night.</p>
                    </div>
                    <div class="bg-slate-950/70 p-3.5 rounded-xl border border-slate-800">
                        <strong class="text-purple-300 block mb-1">☁️ Overcast Transit Suppression (&ge; 90% Clouds)</strong>
                        <p class="text-slate-400">If a transit is predicted during 100% overcast skies or heavy clouds (&ge; 90%), transit alert emails are suppressed. The pass still appears on your dashboard marked with a <span class="text-amber-300 font-semibold">&ldquo;&sim; Cloudy&rdquo;</span> warning. If the weather forecast clears prior to the event, the notification will fire.</p>
                    </div>
                    <div class="bg-slate-950/70 p-3.5 rounded-xl border border-slate-800">
                        <strong class="text-purple-300 block mb-1">🔕 Instant Unsubscribe &amp; Location Toggles</strong>
                        <p class="text-slate-400">Each location has independent toggles for Stargazing Alerts, Solar Transits, and Lunar Transits. Every alert email also contains a secure, one-click link in the footer to manage your preferences or unsubscribe without needing to log in.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 5: Privacy & Data Control -->
    <div x-show="activeTab === 'privacy'" class="space-y-6">
        <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-6 space-y-4">
            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                <span class="text-purple-400">#</span>
                Data Privacy, Zero Tracking &amp; Full User Control
            </h3>
            <p class="text-sm text-slate-300 leading-relaxed">
                Astronotify is committed to absolute data transparency and user sovereignty. Here is how your information is handled:
            </p>

            <div class="space-y-3 pt-2 text-xs">
                <div class="bg-slate-950/70 p-4 rounded-xl border border-slate-800 space-y-1.5">
                    <strong class="text-emerald-400 block font-semibold text-sm">🛡️ Zero Tracking &amp; No Data Harvesting</strong>
                    <p class="text-slate-300 leading-relaxed">
                        We do not track your activity across websites, utilize advertising networks, or install behavioral analytics pixels. We never harvest or compile telemetry on user habits.
                    </p>
                </div>

                <div class="bg-slate-950/70 p-4 rounded-xl border border-slate-800 space-y-1.5">
                    <strong class="text-blue-400 block font-semibold text-sm">🔭 Data Exclusively Used for This Application</strong>
                    <p class="text-slate-300 leading-relaxed">
                        Your account information and location coordinates are used solely to query meteorological weather forecasts and evaluate orbital geometry for the ISS passing across the Sun or Moon. Coordinates are sent securely in server-to-server weather queries without personal identifiers. We do not track your device GPS in the background.
                    </p>
                </div>

                <div class="bg-slate-950/70 p-4 rounded-xl border border-slate-800 space-y-1.5">
                    <strong class="text-amber-400 block font-semibold text-sm">🚫 Strictly No Marketing Emails or Data Selling</strong>
                    <p class="text-slate-300 leading-relaxed">
                        Your data is never sold, leased, or shared with third parties or data brokers. We do not send marketing promotions, sponsored newsletters, or partner pitches. The only emails you ever receive are your chosen stargazing alerts, account security links (like password resets), and rare critical app announcements.
                    </p>
                </div>

                <div class="bg-slate-950/70 p-4 rounded-xl border border-slate-800 space-y-1.5">
                    <strong class="text-purple-400 block font-semibold text-sm">🗑️ Full Control: Instant &amp; Complete Account Deletion</strong>
                    <p class="text-slate-300 leading-relaxed">
                        You can delete your account at any time from your Profile settings. When deleted, your account credentials, all saved observing locations, coordinate thresholds, and cached predictions are erased immediately from our databases. Only anonymous, non-personal aggregate counters remain so the platform's background processing stays stable.
                    </p>
                </div>
            </div>

            <!-- Callout Link to Privacy Policy -->
            <div class="p-4 rounded-xl bg-purple-950/40 border border-purple-800/60 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mt-4">
                <div>
                    <span class="text-sm font-bold text-white block">Looking for the complete legal &amp; technical policy?</span>
                    <span class="text-xs text-slate-400">Read our dedicated Privacy &amp; Data Sovereignty page for full details.</span>
                </div>
                <a href="{{ route('privacy') }}" class="px-4 py-2 rounded-xl text-xs font-bold bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-500 hover:to-purple-500 text-white transition-all shrink-0">
                    Read Privacy Policy &rarr;
                </a>
            </div>
        </div>
    </div>
</div>
