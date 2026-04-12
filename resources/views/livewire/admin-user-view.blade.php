<x-slot name="header">
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-gray-200 leading-tight">
            {{ __('Viewing User: ') }} {{ $user->name }}
        </h2>
        <a href="{{ route('admin.users') }}" class="text-sm text-blue-400 hover:text-blue-300">&larr; Back to Users</a>
    </div>
</x-slot>

<div class="py-12 bg-slate-950 flex-grow text-white">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-slate-800/50 border border-slate-700 rounded-3xl p-6 shadow-xl flex items-center space-x-6">
            @if($user->profile_photo_url)
                <img class="h-24 w-24 rounded-full object-cover" src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" />
            @else
                <div class="h-24 w-24 rounded-full bg-slate-700 flex items-center justify-center text-slate-400 font-bold text-3xl">
                    {{ substr($user->name, 0, 1) }}
                </div>
            @endif
            <div>
                <h3 class="text-2xl font-bold text-white">{{ $user->name }}</h3>
                <p class="text-slate-400">{{ $user->email }}</p>
                <p class="text-sm text-slate-500 mt-2">Joined. {{ $user->created_at->format('M d, Y') }}</p>
                @if($user->is_admin)
                    <span class="inline-block mt-2 bg-blue-500/20 text-blue-300 text-xs px-3 py-1 rounded-full font-bold uppercase tracking-wider">Administrator</span>
                @endif
            </div>
        </div>

        <div class="bg-slate-800/50 border border-slate-700 rounded-3xl p-6 shadow-xl">
            <h3 class="text-xl font-bold mb-6 text-white flex items-center">
                <svg class="w-5 h-5 mr-2 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                Registered Locations
            </h3>
            
            <div class="space-y-4">
                @forelse($user->locations as $location)
                     <div class="p-4 bg-slate-900/50 rounded-xl border border-slate-700">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="text-white font-semibold text-lg">{{ $location->name }}</h4>
                            </div>
                            <div class="text-right text-sm">
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
                    <p class="text-slate-400 italic">This user has not registered any locations yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
