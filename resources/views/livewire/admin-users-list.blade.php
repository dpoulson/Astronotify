<x-slot name="header">
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-gray-200 leading-tight">
            {{ __('Admin: Registered Users') }}
        </h2>
        <a href="{{ route('admin.dashboard') }}" class="text-sm text-blue-400 hover:text-blue-300">&larr; Back to Dashboard</a>
    </div>
</x-slot>

<div class="py-12 bg-slate-950 flex-grow text-white">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-slate-800/50 border border-slate-700 rounded-3xl p-6 shadow-xl">
            <div class="space-y-4">
                @forelse($users as $user)
                    <div class="flex items-center justify-between p-4 bg-slate-900/50 rounded-xl border border-slate-700">
                        <div class="flex items-center space-x-4">
                            @if($user->profile_photo_url)
                                <img class="h-10 w-10 rounded-full object-cover" src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" />
                            @else
                                <div class="h-10 w-10 rounded-full bg-slate-700 flex items-center justify-center text-slate-400 font-bold">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                            @endif
                            <div>
                                <h4 class="text-white font-semibold">{{ $user->name }} @if($user->is_admin) <span class="bg-blue-500/20 text-blue-300 text-xs px-2 py-0.5 rounded-full ml-2">ADMIN</span> @endif</h4>
                                <p class="text-slate-400 text-sm">{{ $user->email }}</p>
                            </div>
                        </div>
                        <div class="text-right flex flex-col items-end space-y-2">
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.user.view', $user->id) }}" class="px-3 py-1 bg-blue-600/20 text-blue-400 rounded-lg hover:bg-blue-600/40 text-xs font-bold transition">View</a>
                                <button wire:click="deleteUser({{ $user->id }})" wire:confirm="Are you sure you want to delete this user and all their locations?" class="px-3 py-1 bg-red-600/20 text-red-400 rounded-lg hover:bg-red-600/40 text-xs font-bold transition">Delete</button>
                            </div>
                            <div class="text-right mt-1">
                                <span class="text-2xl font-bold {{ $user->locations_count > 0 ? 'text-purple-400' : 'text-slate-500' }}">
                                    {{ $user->locations_count }}
                                </span>
                                <p class="text-xs text-slate-500 uppercase">Locations</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-slate-400">No users found.</p>
                @endforelse
            </div>
            
            <div class="mt-6">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
