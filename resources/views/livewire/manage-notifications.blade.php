<div class="min-h-screen bg-slate-950 flex flex-col justify-center items-center p-4">
    <div class="w-full max-w-xl bg-slate-900/60 backdrop-blur-xl border border-slate-800 rounded-3xl p-8 shadow-2xl space-y-6">
        
        <!-- Header -->
        <div class="text-center space-y-2">
            <h1 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-400">
                Astronotify
            </h1>
            <h2 class="text-lg font-bold text-white">
                Manage Notification Preferences
            </h2>
            <p class="text-xs text-slate-400">
                Configure email alerts for user <span class="text-purple-300 font-semibold">{{ $user->name }}</span> ({{ $user->email }})
            </p>
        </div>

        @if($saved)
            <div class="bg-emerald-950/60 border border-emerald-800 rounded-2xl p-6 text-center space-y-4">
                <span class="text-4xl">✅</span>
                <h3 class="text-lg font-bold text-emerald-300">Preferences Saved!</h3>
                <p class="text-sm text-slate-300">Your notification settings have been updated successfully. You can close this window now.</p>
            </div>
        @else
            <form wire:submit="save" class="space-y-6">
                @if(empty($preferences))
                    <div class="text-center py-6 text-slate-500 text-sm">
                        You don't have any locations registered yet.
                    </div>
                @else
                    <div class="space-y-4 max-h-96 overflow-y-auto pr-1">
                        @foreach($preferences as $locationId => $prefs)
                            <div class="bg-slate-950/60 border border-slate-800 rounded-2xl p-5 space-y-4">
                                <h4 class="font-bold text-slate-200 border-b border-slate-800 pb-2 text-sm">
                                    📍 {{ $prefs['name'] }}
                                </h4>
                                
                                <div class="space-y-3">
                                    <label class="relative flex items-start cursor-pointer select-none">
                                        <div class="flex items-center h-5">
                                            <input type="checkbox" wire:model="preferences.{{ $locationId }}.notify_stargazing_alerts" class="w-4 h-4 rounded border-slate-700 bg-slate-900 text-purple-600 focus:ring-purple-500 cursor-pointer">
                                        </div>
                                        <div class="ms-3 text-xs">
                                            <span class="font-medium text-slate-300">Stargazing Weather Alerts 🔔</span>
                                            <span class="block text-xs text-slate-500">Alerts when stargazing conditions are optimal.</span>
                                        </div>
                                    </label>

                                    <label class="relative flex items-start cursor-pointer select-none">
                                        <div class="flex items-center h-5">
                                            <input type="checkbox" wire:model="preferences.{{ $locationId }}.notify_iss_sun_transit" class="w-4 h-4 rounded border-slate-700 bg-slate-900 text-purple-600 focus:ring-purple-500 cursor-pointer">
                                        </div>
                                        <div class="ms-3 text-xs">
                                            <span class="font-medium text-slate-300">ISS Solar Transits ☀️</span>
                                            <span class="block text-xs text-slate-500">Alerts when the ISS passes in front of the Sun.</span>
                                        </div>
                                    </label>

                                    <label class="relative flex items-start cursor-pointer select-none">
                                        <div class="flex items-center h-5">
                                            <input type="checkbox" wire:model="preferences.{{ $locationId }}.notify_iss_moon_transit" class="w-4 h-4 rounded border-slate-700 bg-slate-900 text-purple-600 focus:ring-purple-500 cursor-pointer">
                                        </div>
                                        <div class="ms-3 text-xs">
                                            <span class="font-medium text-slate-300">ISS Lunar Transits 🌙</span>
                                            <span class="block text-xs text-slate-500">Alerts when the ISS passes in front of the Moon.</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="pt-2">
                    <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-500 hover:to-purple-500 text-white font-bold py-3 px-4 rounded-xl shadow-lg transition-all transform hover:scale-[1.01]">
                        Save Preferences
                    </button>
                </div>
            </form>
        @endif

        <!-- Footer link -->
        <div class="text-center text-[10px] text-slate-600">
            &copy; {{ date('Y') }} Astronotify. All rights reserved.
        </div>
    </div>
</div>
