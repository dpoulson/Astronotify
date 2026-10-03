<x-slot name="header">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="font-bold text-2xl text-white tracking-tight flex items-center gap-2">
                <span>🌌</span> {{ __('Curated Stargazing Spots') }}
            </h2>
            <p class="text-xs text-slate-400 mt-1">
                {{ $activeSpots }} active spots of {{ $totalSpots }} total worldwide locations
            </p>
        </div>
        <div class="flex items-center gap-3">
            <button 
                wire:click="openCreateModal" 
                class="px-4 py-2 bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-500 hover:to-blue-500 text-white rounded-xl text-xs font-bold transition shadow-md flex items-center gap-1.5"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Add Stargazing Spot</span>
            </button>
            <a href="{{ route('admin.dashboard') }}" class="text-xs font-semibold text-slate-400 hover:text-white px-3 py-2 rounded-xl bg-slate-800/80 border border-slate-700 transition">
                &larr; Admin
            </a>
        </div>
    </div>
</x-slot>

<div class="py-8 bg-slate-950 flex-grow text-white">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

        @if (session()->has('message'))
            <div class="p-4 rounded-2xl bg-emerald-950/80 border border-emerald-500/50 text-emerald-200 text-sm flex items-center justify-between">
                <span>✓ {{ session('message') }}</span>
                <button type="button" @click="$el.parentElement.remove()" class="text-emerald-400 hover:text-emerald-100">&times;</button>
            </div>
        @endif

        {{-- Filters & Search --}}
        <div class="p-4 bg-slate-900/80 border border-slate-800 rounded-2xl flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="w-full md:w-96 relative">
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Search by spot name, country, or park..." 
                    class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-purple-500"
                />
            </div>
            <div class="flex items-center gap-3 w-full md:w-auto">
                <select 
                    wire:model.live="countryFilter" 
                    class="bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-purple-500"
                >
                    <option value="">All Countries</option>
                    @foreach($countries as $c)
                        <option value="{{ $c }}">{{ $c }}</option>
                    @endforeach
                </select>

                <select 
                    wire:model.live="bortleFilter" 
                    class="bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-purple-500"
                >
                    <option value="">All Bortle Classes</option>
                    @for($b = 1; $b <= 5; $b++)
                        <option value="{{ $b }}">Bortle {{ $b }}</option>
                    @endfor
                </select>

                <a 
                    href="{{ route('spots.index') }}" 
                    target="_blank" 
                    class="text-xs text-purple-400 hover:text-purple-300 font-semibold px-3 py-2 rounded-xl bg-purple-950/40 border border-purple-500/30 whitespace-nowrap"
                >
                    View Public Directory ↗
                </a>
            </div>
        </div>

        {{-- Spots Table --}}
        <div class="bg-slate-900/60 border border-slate-800 rounded-3xl overflow-hidden shadow-2xl">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm text-slate-300">
                    <thead class="text-xs uppercase bg-slate-950/80 text-slate-400 border-b border-slate-800">
                        <tr>
                            <th class="px-6 py-4 whitespace-nowrap">Spot &amp; Region</th>
                            <th class="px-6 py-4 whitespace-nowrap">Dark Sky Status</th>
                            <th class="px-6 py-4 whitespace-nowrap">Bortle</th>
                            <th class="px-6 py-4 whitespace-nowrap">Coordinates / Elev</th>
                            <th class="px-6 py-4 whitespace-nowrap">Status</th>
                            <th class="px-6 py-4 whitespace-nowrap text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse($spots as $spot)
                            <tr class="hover:bg-slate-800/40 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-bold text-white flex items-center gap-1.5">
                                        <a href="{{ route('spots.show', $spot->slug) }}" target="_blank" class="hover:text-purple-400 transition underline decoration-slate-700 underline-offset-2">
                                            {{ $spot->name }}
                                        </a>
                                    </div>
                                    <div class="text-xs text-slate-400 mt-0.5">
                                        {{ $spot->region ? $spot->region . ', ' : '' }}{{ $spot->country }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs">
                                    @if($spot->dark_sky_status)
                                        <span class="inline-block px-2.5 py-1 rounded-lg bg-indigo-950/80 text-indigo-300 border border-indigo-500/30 font-medium">
                                            {{ $spot->dark_sky_status }}
                                        </span>
                                    @else
                                        <span class="text-slate-600">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold border {{ $spot->bortle_color }}">
                                        Bortle {{ $spot->bortle_class }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs font-mono text-slate-400">
                                    <div>{{ $spot->latitude }}°, {{ $spot->longitude }}°</div>
                                    <div class="text-slate-500">{{ $spot->elevation }}m elev</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button 
                                        type="button" 
                                        wire:click="toggleActive({{ $spot->id }})" 
                                        class="px-2.5 py-1 rounded-lg text-xs font-semibold transition {{ $spot->is_active ? 'bg-emerald-950 text-emerald-400 border border-emerald-500/30' : 'bg-rose-950 text-rose-400 border border-rose-500/30' }}"
                                    >
                                        {{ $spot->is_active ? 'Active' : 'Disabled' }}
                                    </button>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                                    <button 
                                        wire:click="editSpot({{ $spot->id }})" 
                                        class="px-3 py-1.5 rounded-lg bg-blue-950/80 hover:bg-blue-900 border border-blue-500/40 text-blue-300 text-xs font-semibold transition"
                                    >
                                        Edit
                                    </button>
                                    <button 
                                        wire:click="deleteSpot({{ $spot->id }})" 
                                        wire:confirm="Permanently delete '{{ $spot->name }}'?" 
                                        class="px-3 py-1.5 rounded-lg bg-red-950/80 hover:bg-red-900 border border-red-500/40 text-red-300 text-xs font-semibold transition"
                                    >
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                    No stargazing spots found matching your query.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-6 border-t border-slate-800">
                {{ $spots->links() }}
            </div>
        </div>
    </div>

    {{-- Create / Edit Spot Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md overflow-y-auto">
            <div class="bg-slate-900 border border-slate-700 rounded-3xl p-6 sm:p-8 max-w-2xl w-full shadow-2xl relative my-8">
                <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <span>✨</span> {{ $editingSpotId ? 'Edit Stargazing Spot' : 'Add New Stargazing Spot' }}
                    </h3>
                    <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-white text-xl leading-none">&times;</button>
                </div>

                <form wire:submit="save" class="mt-6 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-slate-300">Spot Name *</label>
                            <input type="text" wire:model="name" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-purple-500" placeholder="e.g. Kielder Observatory" />
                            @error('name') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-slate-300">URL Slug (leave blank to auto-generate)</label>
                            <input type="text" wire:model="slug" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-purple-500" placeholder="e.g. kielder-observatory" />
                            @error('slug') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-slate-300">Country *</label>
                            <input type="text" wire:model="country" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-purple-500" placeholder="e.g. United Kingdom" />
                            @error('country') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-slate-300">Region / State / County</label>
                            <input type="text" wire:model="region" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-purple-500" placeholder="e.g. Northumberland" />
                            @error('region') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-slate-300">Latitude (-90 to 90) *</label>
                            <input type="number" step="0.000001" wire:model="latitude" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-purple-500" placeholder="55.2333" />
                            @error('latitude') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-slate-300">Longitude (-180 to 180) *</label>
                            <input type="number" step="0.000001" wire:model="longitude" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-purple-500" placeholder="-2.5833" />
                            @error('longitude') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-slate-300">Elevation (meters)</label>
                            <input type="number" wire:model="elevation" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-purple-500" placeholder="360" />
                            @error('elevation') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-slate-300">Bortle Scale Class (1-9) *</label>
                            <select wire:model="bortle_class" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-purple-500">
                                <option value="1">Class 1: Excellent truly dark sky</option>
                                <option value="2">Class 2: Truly dark site</option>
                                <option value="3">Class 3: Rural sky</option>
                                <option value="4">Class 4: Rural/suburban transition</option>
                                <option value="5">Class 5: Suburban sky</option>
                                <option value="6">Class 6: Bright suburban sky</option>
                                <option value="7">Class 7: Suburban/urban transition</option>
                            </select>
                            @error('bortle_class') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-300">Dark Sky Status / Designation</label>
                        <input type="text" wire:model="dark_sky_status" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-purple-500" placeholder="e.g. IDA Gold Tier Dark Sky Park" />
                        @error('dark_sky_status') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-300">Description &amp; Highlights</label>
                        <textarea wire:model="description" rows="3" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-purple-500" placeholder="Why this site is notable for observing, horizons, access notes..."></textarea>
                        @error('description') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <input type="checkbox" id="spot_is_active" wire:model="is_active" class="rounded bg-slate-950 border-slate-700 text-purple-600 focus:ring-purple-500" />
                        <label for="spot_is_active" class="text-xs text-slate-300">Active and visible in public directory</label>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                        <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:text-white transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-500 hover:to-blue-500 text-white transition shadow-lg">
                            {{ $editingSpotId ? 'Save Changes' : 'Create Spot' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
