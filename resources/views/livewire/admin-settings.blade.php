<div class="py-12 bg-slate-950 flex-grow text-white">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-slate-800/50 border border-slate-700 rounded-3xl p-6 shadow-xl">
            <h2 class="text-3xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-500 mb-6 flex items-center">
                System Logic Parameters
            </h2>

            @if (session()->has('message'))
                <div class="mb-4 bg-green-500/20 border border-green-500/50 text-green-300 p-4 rounded-xl backdrop-blur-md">
                    {{ session('message') }}
                </div>
            @endif

            <form wire:submit="save" class="space-y-6">
                <div class="p-6 bg-slate-900/60 rounded-2xl border border-slate-700">
                    <h3 class="text-xl font-semibold mb-4 text-slate-200">Cron Configurations</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2" title="How many days ahead the Open-Meteo API should fetch?">Forecast Window (Days)</label>
                            <input type="number" wire:model="forecast_days" class="w-full bg-slate-800 border border-slate-600 rounded-xl text-white focus:ring-purple-500 focus:border-purple-500" min="1" max="16">
                            <p class="text-xs text-slate-400 mt-2">Maximum 16 days strictly allowed by the API engine payload.</p>
                            @error('forecast_days') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2" title="Precision for geospatial bounds in the backend DB groupings">Coordinate Grouping (Decimals)</label>
                            <input type="number" wire:model="grouping_decimal_places" class="w-full bg-slate-800 border border-slate-600 rounded-xl text-white focus:ring-purple-500 focus:border-purple-500" min="0" max="4">
                            <p class="text-xs text-slate-400 mt-2">Setting 1 generates ~11km bounds. Setting 0 forces ~111km coarse limits.</p>
                            @error('grouping_decimal_places') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-500 hover:to-purple-500 text-white font-bold py-3 px-8 rounded-xl shadow-lg transition-transform transform hover:scale-105">
                        Save Configurations
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
