<x-slot name="header">
    <h2 class="font-semibold text-xl text-slate-800 dark:text-gray-200 leading-tight">
        {{ __('Email Queue') }}
    </h2>
</x-slot>

<div class="py-12 bg-slate-950 min-h-screen text-white">
    <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
        
        <div class="flex justify-between items-end mb-8">
            <div>
                <h1 class="text-3xl font-extrabold text-white">Pending Emails</h1>
                <p class="text-slate-400 mt-1 text-sm">Monitor and manage outbound stargazing alerts.</p>
            </div>
            <div class="flex space-x-4">
                <button wire:click="$refresh" class="inline-flex items-center px-4 py-2 bg-slate-800 border border-slate-700 rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-slate-700 transition ease-in-out duration-150 shadow-lg">
                    Refresh
                </button>
                <button wire:click="clearQueue" wire:confirm="Are you sure you want to clear the entire queue?" class="inline-flex items-center px-4 py-2 bg-red-600/20 border border-red-600/50 rounded-xl font-bold text-xs text-red-400 uppercase tracking-widest hover:bg-red-600 hover:text-white transition ease-in-out duration-150 shadow-lg">
                    Clear All
                </button>
            </div>
        </div>
        
        @if (session()->has('message'))
            <div class="mb-4 px-4 py-2 bg-green-900/50 border border-green-700 text-green-300 rounded-lg">
                {{ session('message') }}
            </div>
        @endif

        <div class="bg-slate-900 border border-slate-800 overflow-hidden shadow-xl sm:rounded-3xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-800/50">
                            <th class="px-6 py-4 text-slate-400 font-semibold uppercase text-xs tracking-wider border-b border-slate-800">ID</th>
                            <th class="px-6 py-4 text-slate-400 font-semibold uppercase text-xs tracking-wider border-b border-slate-800">Mailable / Job Type</th>
                            <th class="px-6 py-4 text-slate-400 font-semibold uppercase text-xs tracking-wider border-b border-slate-800">Recipient</th>
                            <th class="px-6 py-4 text-slate-400 font-semibold uppercase text-xs tracking-wider border-b border-slate-800 text-center">Attempts</th>
                            <th class="px-6 py-4 text-slate-400 font-semibold uppercase text-xs tracking-wider border-b border-slate-800">Created At</th>
                            <th class="px-6 py-4 text-slate-400 font-semibold uppercase text-xs tracking-wider border-b border-slate-800 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse ($jobs as $job)
                            <tr class="hover:bg-slate-800/30 transition-colors">
                                <td class="px-6 py-4 text-slate-500 font-mono text-sm">#{{ $job->id }}</td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-white">
                                        @if(isset($job->mailable_class))
                                            {{ str_replace('App\\Mail\\', '', $job->mailable_class) }}
                                        @else
                                            {{ str_replace('Illuminate\\Mail\\', '', $job->display_name) }}
                                        @endif
                                    </div>
                                    <div class="text-xs text-slate-500 font-mono mt-1">Class: {{ $job->display_name }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    @if(isset($job->recipient))
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-900/30 text-blue-300 border border-blue-800">
                                            {{ $job->recipient }}
                                        </span>
                                    @else
                                        <span class="text-slate-600 italic text-sm">N/A</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-sm {{ $job->attempts > 0 ? 'text-yellow-400 font-bold' : 'text-slate-400' }}">
                                        {{ $job->attempts }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-400">
                                    {{ \Carbon\Carbon::createFromTimestamp($job->created_at)->diffForHumans() }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button wire:click="deleteJob({{ $job->id }})" wire:confirm="Delete this job?" class="text-red-400 hover:text-red-300 transition-colors text-sm font-semibold">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 mb-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L22 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        <span class="text-lg">The email queue is currently empty.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($jobs->hasPages())
                <div class="px-6 py-4 bg-slate-800/20 border-t border-slate-800">
                    {{ $jobs->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
