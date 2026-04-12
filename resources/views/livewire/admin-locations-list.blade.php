<x-slot name="header">
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-gray-200 leading-tight">
            {{ __('Admin: Registered Locations') }}
        </h2>
        <a href="{{ route('admin.dashboard') }}" class="text-sm text-blue-400 hover:text-blue-300">&larr; Back to Dashboard</a>
    </div>
</x-slot>

<div class="py-12 bg-slate-950 flex-grow text-white">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-slate-800/50 border border-slate-700 rounded-3xl p-6 shadow-xl">
            <div class="space-y-4">
                @forelse($locations as $location)
                    <div class="p-4 bg-slate-900/50 rounded-xl border border-slate-700">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="text-white font-semibold text-lg">{{ $location->name }}</h4>
                                <p class="text-slate-400 text-sm mt-1">Owned by: <span class="text-slate-300">{{ $location->user->name }}</span></p>
                            </div>
                            <div class="text-right text-sm flex flex-col items-end">
                                <button wire:click="deleteLocation({{ $location->id }})" wire:confirm="Are you sure you want to delete this location?" class="mb-2 px-3 py-1 bg-red-600/20 text-red-400 rounded-lg hover:bg-red-600/40 text-xs font-bold transition">Delete</button>
                                <p class="text-slate-400">Lat: <span class="text-slate-200">{{ $location->latitude }}</span></p>
                                <p class="text-slate-400">Lon: <span class="text-slate-200">{{ $location->longitude }}</span></p>
                            </div>
                        </div>
                        <div class="mt-3 grid grid-cols-4 gap-2 text-xs text-center border-t border-slate-700 pt-3">
                            <div><span class="block text-slate-500">Night</span><span class="text-blue-300 font-bold">{{ $location->min_night_length_hours }}h</span></div>
                            <div><span class="block text-slate-500">Clear</span><span class="text-purple-300 font-bold">{{ $location->min_clear_hours }}h</span></div>
                            <div><span class="block text-slate-500">Wind</span><span class="text-teal-300 font-bold"><{{ $location->max_wind_speed }}</span></div>
                            <div><span class="block text-slate-500">Cloud</span><span class="text-gray-300 font-bold"><{{ $location->max_cloud_cover }}%</span></div>
                        </div>
                    </div>
                @empty
                    <p class="text-slate-400">No locations registered yet.</p>
                @endforelse
            </div>
            
            <div class="mt-6">
                {{ $locations->links() }}
            </div>
        </div>
    </div>
</div>
