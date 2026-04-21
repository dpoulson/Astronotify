<x-slot name="header">
    <h2 class="font-semibold text-xl text-slate-800 dark:text-gray-200 leading-tight">
        {{ __('Cron & Command Monitor') }}
    </h2>
</x-slot>

<div class="py-12 bg-slate-950 min-h-screen text-white">
    <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
        
        <div class="flex justify-between items-end mb-8">
            <div>
                <h1 class="text-3xl font-extrabold text-white">Execution History</h1>
                <p class="text-slate-400 mt-1 text-sm">Monitor scheduled tasks and background artisan commands.</p>
            </div>
            <div class="flex space-x-4">
                <button wire:click="$refresh" class="inline-flex items-center px-4 py-2 bg-slate-800 border border-slate-700 rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-slate-700 transition ease-in-out duration-150 shadow-lg">
                    Refresh
                </button>
                <button wire:click="clearLogs" wire:confirm="Are you sure you want to clear all history?" class="inline-flex items-center px-4 py-2 bg-red-600/20 border border-red-600/50 rounded-xl font-bold text-xs text-red-400 uppercase tracking-widest hover:bg-red-600 hover:text-white transition ease-in-out duration-150 shadow-lg">
                    Clear Logs
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
                            <th class="px-6 py-4 text-slate-400 font-semibold uppercase text-xs tracking-wider border-b border-slate-800">Command</th>
                            <th class="px-6 py-4 text-slate-400 font-semibold uppercase text-xs tracking-wider border-b border-slate-800 text-center">Status</th>
                            <th class="px-6 py-4 text-slate-400 font-semibold uppercase text-xs tracking-wider border-b border-slate-800 text-center">Duration</th>
                            <th class="px-6 py-4 text-slate-400 font-semibold uppercase text-xs tracking-wider border-b border-slate-800">Started</th>
                            <th class="px-6 py-4 text-slate-400 font-semibold uppercase text-xs tracking-wider border-b border-slate-800 border-r border-slate-800">Finished</th>
                            <th class="px-6 py-4 text-slate-400 font-semibold uppercase text-xs tracking-wider border-b border-slate-800 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse ($logs as $log)
                            <tr class="hover:bg-slate-800/30 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-white font-mono break-all">
                                        php artisan {{ $log->command }}
                                    </div>
                                    @if($log->exit_code !== null)
                                        <div class="text-[10px] text-slate-500 mt-1 uppercase tracking-tighter">Exit Code: {{ $log->exit_code }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($log->status === 'running')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-900 text-blue-300 border border-blue-700 animate-pulse">
                                            RUNNING
                                        </span>
                                    @elseif($log->status === 'success')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-900/40 text-emerald-400 border border-emerald-800">
                                            SUCCESS
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-red-900/40 text-red-400 border border-red-800">
                                            FAILED
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center font-mono text-sm">
                                    @if($log->duration_ms)
                                        <span class="{{ $log->duration_ms > 5000 ? 'text-yellow-400' : 'text-slate-400' }}">
                                            {{ number_format($log->duration_ms / 1000, 2) }}s
                                        </span>
                                    @else
                                        <span class="text-slate-600 italic">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-400">
                                    {{ $log->started_at->format('M d, H:i:s') }}
                                    <div class="text-[10px] text-slate-600">{{ $log->started_at->diffForHumans() }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-400 border-r border-slate-800">
                                    @if($log->finished_at)
                                        {{ $log->finished_at->format('H:i:s') }}
                                    @else
                                        <span class="text-slate-600 italic">Pending...</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button wire:click="deleteLog({{ $log->id }})" class="text-slate-600 hover:text-red-400 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-500 italic">
                                    No command execution history tracked yet. Run a command to see it here.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($logs->hasPages())
                <div class="px-6 py-4 bg-slate-800/20 border-t border-slate-800">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
