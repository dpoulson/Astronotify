<div class="p-6 lg:p-8 bg-slate-900 border border-slate-800 text-white relative overflow-hidden sm:rounded-3xl shadow-2xl">
    <!-- Background Accents -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
        <div class="absolute top-[-20%] left-[-10%] w-[50%] h-[50%] bg-purple-900/30 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-[-20%] right-[-10%] w-[60%] h-[60%] bg-blue-900/20 rounded-full blur-[150px]"></div>
    </div>

    <div class="relative z-10 w-full space-y-12">
        <!-- Header -->
        <div class="text-center">
            <h1 class="text-4xl font-extrabold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-500">
                Stargazing Locations
            </h1>
            <p class="mt-2 text-lg text-slate-400">Configure your viewing spots and sky conditions.</p>
        </div>

        @if (session()->has('message'))
            <div class="bg-green-500/20 border border-green-500/50 text-green-300 p-4 rounded-xl backdrop-blur-md">
                {{ session('message') }}
            </div>
        @endif

        <!-- Upcoming Nights Panel -->
        @if($upcomingNights->count() > 0)
        <div class="bg-gradient-to-r from-blue-900/40 to-purple-900/40 border border-purple-500/30 rounded-3xl p-6 shadow-2xl backdrop-blur-xl mb-8">
            <h2 class="text-xl font-bold text-white mb-4 flex items-center">
                <svg class="w-5 h-5 mr-3 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" /></svg>
                Upcoming Optimal Nights
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
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
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Form Card -->
            <div class="md:col-span-1">
                <div class="bg-slate-800/40 backdrop-blur-xl border border-slate-700/50 rounded-3xl p-6 shadow-2xl">
                    <h2 class="text-xl font-bold text-white mb-6">{{ $editingLocationId ? 'Edit Location' : 'Add New Location' }}</h2>
                    
                    <form wire:submit="save" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300">Location Name</label>
                            <input type="text" wire:model="name" class="mt-1 block w-full bg-slate-900/50 border border-slate-700 rounded-xl text-white focus:ring-purple-500 focus:border-purple-500 placeholder-slate-500" placeholder="e.g. Home">
                            @error('name') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300">Search Town/City</label>
                            <input type="text" wire:model.live.debounce.500ms="town" class="mt-1 block w-full bg-slate-900/50 border border-slate-700 rounded-xl text-white focus:ring-purple-500 focus:border-purple-500 placeholder-slate-500" placeholder="e.g. Lancaster, UK">
                            <p class="text-xs text-slate-400 mt-1">Typing a town will auto-fill the coordinates below.</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
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
            </div>

            <!-- Locations List -->
            <div class="md:col-span-2 space-y-6">
                @forelse($locations as $location)
                    <div class="bg-slate-800/30 backdrop-blur-xl border border-slate-700/50 rounded-3xl p-6 shadow-xl hover:bg-slate-800/50 transition-all flex flex-col justify-between items-start md:flex-row md:items-center">
                        <div>
                            <h3 class="text-2xl font-bold text-white">{{ $location->name }}</h3>
                            <div class="text-slate-400 text-sm mt-1 mb-3 flex space-x-4">
                                <span>Lat: <span class="text-slate-200">{{ $location->latitude }}</span></span>
                                <span>Lon: <span class="text-slate-200">{{ $location->longitude }}</span></span>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs text-slate-300">
                                <div class="bg-slate-900/60 p-2 rounded-lg border border-slate-700">🌙 Min Night: <span class="font-bold text-blue-300">{{ $location->min_night_length_hours }}h</span></div>
                                <div class="bg-slate-900/60 p-2 rounded-lg border border-slate-700">✨ Clear: <span class="font-bold text-purple-300">{{ $location->min_clear_hours }}h+</span></div>
                                <div class="bg-slate-900/60 p-2 rounded-lg border border-slate-700">💨 Wind: <span class="font-bold text-teal-300"><{{ $location->max_wind_speed }}</span></div>
                                <div class="bg-slate-900/60 p-2 rounded-lg border border-slate-700">☁️ Clouds: <span class="font-bold text-gray-300"><{{ $location->max_cloud_cover }}%</span></div>
                            </div>
                        </div>
                        <div class="mt-4 md:mt-0 flex shrink-0 border border-slate-700 p-1 space-x-1 rounded-xl bg-slate-900/50">
                            <button wire:click="edit({{ $location->id }})" class="p-2 text-blue-400 hover:text-blue-300 hover:bg-blue-900/30 rounded-lg transition-colors" title="Edit">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </button>
                            <button wire:click="delete({{ $location->id }})" wire:confirm="Are you sure you want to delete this location?" class="p-2 text-red-400 hover:text-red-300 hover:bg-red-900/30 rounded-lg transition-colors" title="Delete">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center p-12 bg-slate-800/20 backdrop-blur-sm border border-dashed border-slate-700 rounded-3xl text-slate-500">
                        <svg class="w-16 h-16 mb-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
                        <p class="text-lg">No locations saved yet.</p>
                        <p class="text-sm">Add one to start monitoring stargazing conditions.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
