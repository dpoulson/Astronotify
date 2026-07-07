<x-slot name="header">
    <h2 class="font-semibold text-xl text-slate-800 dark:text-gray-200 leading-tight">
        {{ __('Email Queue & Diagnostics') }}
    </h2>
</x-slot>

<div class="py-12 bg-slate-950 min-h-screen text-white">
    <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
        
        <div class="flex justify-between items-end mb-8">
            <div>
                <h1 class="text-3xl font-extrabold text-white">Email Queue & Diagnostics</h1>
                <p class="text-slate-400 mt-1 text-sm">Monitor, test, and troubleshoot outbound email notifications.</p>
            </div>
            <div class="flex space-x-4">
                <button wire:click="$refresh" class="inline-flex items-center px-4 py-2 bg-slate-800 border border-slate-700 rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-slate-700 transition ease-in-out duration-150 shadow-lg">
                    Refresh
                </button>
            </div>
        </div>
        
        @if (session()->has('message'))
            <div class="mb-6 px-4 py-3 bg-green-900/40 border border-green-700/60 text-green-300 rounded-2xl shadow-lg">
                {{ session('message') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-6 px-4 py-3 bg-red-900/40 border border-red-700/60 text-red-300 rounded-2xl shadow-lg">
                {{ session('error') }}
            </div>
        @endif

        <!-- Diagnostic Controls -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- Left 2 cols: Mail Tester -->
            <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-500/5 rounded-full blur-2xl"></div>
                <h2 class="text-xl font-bold text-white mb-2 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                    </svg>
                    Mail Server Connection Diagnostics
                </h2>
                <p class="text-slate-400 text-sm mb-4">Send a synchronous test email directly from this server. This bypasses the queue to immediately verify if your SMTP configuration in the <code class="bg-slate-950 px-1 py-0.5 rounded text-indigo-300 font-mono">.env</code> file is correct.</p>
                
                <form wire:submit.prevent="sendTestEmail" class="space-y-4">
                    <div class="flex flex-col sm:flex-row gap-2">
                        <div class="relative flex-1">
                            <input type="email" wire:model="testEmailAddress" placeholder="receiver@example.com" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors" />
                            @error('testEmailAddress')
                                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white px-5 py-2.5 rounded-xl font-semibold text-sm transition-colors shadow-lg flex items-center justify-center min-w-[140px]">
                            @if($testEmailStatus === 'sending')
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Sending...
                            @else
                                Send Test Email
                            @endif
                        </button>
                    </div>
                </form>

                @if($testEmailStatus === 'success')
                    <div class="mt-4 p-4 bg-green-950/40 border border-green-800/60 rounded-xl text-green-300 text-sm">
                        <strong>✅ Test email sent successfully!</strong> Please check your mailbox (including your spam folder) for "Astronotify Mail Connection Test 🚀".
                    </div>
                @elseif($testEmailStatus === 'error')
                    <div class="mt-4 p-4 bg-red-950/40 border border-red-800/60 rounded-xl text-red-300 text-sm space-y-2">
                        <div>
                            <strong>❌ Error sending email:</strong>
                            <span class="block mt-1 font-bold text-white">{{ strtok($testEmailErrorDetails, "\n") }}</span>
                        </div>
                        <details class="group">
                            <summary class="cursor-pointer text-xs text-red-400 hover:underline select-none">View full error stack trace</summary>
                            <pre class="mt-2 p-3 bg-slate-950 text-slate-300 font-mono text-xs rounded-lg overflow-x-auto whitespace-pre-wrap max-h-60 border border-red-900/40">{{ $testEmailErrorDetails }}</pre>
                        </details>
                    </div>
                @endif
            </div>

            <!-- Right 1 col: System Info Summary -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl flex flex-col justify-between relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/5 rounded-full blur-2xl"></div>
                <div>
                    <h2 class="text-xl font-bold text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Environment Info
                    </h2>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between border-b border-slate-800 pb-2">
                            <span class="text-slate-400">Mail default driver</span>
                            <span class="font-mono text-emerald-400 font-bold bg-slate-950 px-2 py-0.5 rounded text-xs">{{ config('mail.default') }}</span>
                        </div>
                        @if(config('mail.default') === 'smtp')
                        <div class="flex justify-between border-b border-slate-800 pb-2">
                            <span class="text-slate-400">SMTP Host</span>
                            <span class="font-mono text-slate-200 text-xs">{{ config('mail.mailers.smtp.host') }}:{{ config('mail.mailers.smtp.port') }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between border-b border-slate-800 pb-2">
                            <span class="text-slate-400">Queue Connection</span>
                            <span class="font-mono text-emerald-400 font-bold bg-slate-950 px-2 py-0.5 rounded text-xs">{{ config('queue.default') }}</span>
                        </div>
                        <div class="flex justify-between pb-1">
                            <span class="text-slate-400">Queue Driver</span>
                            <span class="font-mono text-slate-200 text-xs">
                                @if(config('queue.default') === 'database')
                                    database (requires queue worker)
                                @else
                                    {{ config('queue.default') }}
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
                <div class="mt-4 text-xs text-slate-500 border-t border-slate-800 pt-3">
                    💡 If using <code class="bg-slate-950 text-slate-400 px-1 py-0.2 rounded font-mono">database</code> queue, run <code class="bg-slate-950 text-indigo-400 px-1 py-0.2 rounded font-mono">php artisan queue:work</code> on your server to process queued emails.
                </div>
            </div>
        </div>

        <!-- Pending Emails Card -->
        <div class="mb-10">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold text-white flex items-center">
                    <span class="w-3 h-3 bg-yellow-500 rounded-full mr-2.5 animate-pulse"></span>
                    Pending Emails ({{ $jobs->total() }})
                </h2>
                @if($jobs->total() > 0)
                    <button wire:click="clearQueue" wire:confirm="Are you sure you want to delete all pending jobs in the queue?" class="text-xs bg-red-950/40 hover:bg-red-900 border border-red-800 text-red-300 font-bold px-3 py-1.5 rounded-xl uppercase tracking-wider transition-colors shadow">
                        Clear Pending Queue
                    </button>
                @endif
            </div>
            
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
                                            <span class="text-lg">No pending emails in the queue.</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($jobs->hasPages())
                    <div class="px-6 py-4 bg-slate-800/20 border-t border-slate-800">
                        {{ $jobs->links(data: ['pageName' => 'pendingPage']) }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Failed Emails Card -->
        <div>
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold text-white flex items-center">
                    <span class="w-3 h-3 bg-red-500 rounded-full mr-2.5 animate-pulse"></span>
                    Failed Emails ({{ $failedJobs->total() }})
                </h2>
                @if($failedJobs->total() > 0)
                    <button wire:click="clearFailedJobs" wire:confirm="Are you sure you want to delete all failed jobs permanently?" class="text-xs bg-red-950/40 hover:bg-red-900 border border-red-800 text-red-300 font-bold px-3 py-1.5 rounded-xl uppercase tracking-wider transition-colors shadow">
                        Clear Failed History
                    </button>
                @endif
            </div>
            
            <div class="bg-slate-900 border border-slate-800 overflow-hidden shadow-xl sm:rounded-3xl">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-800/50">
                                <th class="px-6 py-4 text-slate-400 font-semibold uppercase text-xs tracking-wider border-b border-slate-800">UUID</th>
                                <th class="px-6 py-4 text-slate-400 font-semibold uppercase text-xs tracking-wider border-b border-slate-800">Mailable</th>
                                <th class="px-6 py-4 text-slate-400 font-semibold uppercase text-xs tracking-wider border-b border-slate-800">Recipient</th>
                                <th class="px-6 py-4 text-slate-400 font-semibold uppercase text-xs tracking-wider border-b border-slate-800">Failed At</th>
                                <th class="px-6 py-4 text-slate-400 font-semibold uppercase text-xs tracking-wider border-b border-slate-800">Exception</th>
                                <th class="px-6 py-4 text-slate-400 font-semibold uppercase text-xs tracking-wider border-b border-slate-800 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            @forelse ($failedJobs as $fjob)
                                <tr class="hover:bg-slate-800/30 transition-colors">
                                    <td class="px-6 py-4 text-slate-500 font-mono text-xs select-all max-w-[120px] truncate" title="{{ $fjob->uuid }}">{{ $fjob->uuid }}</td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-white">
                                            @if(isset($fjob->mailable_class))
                                                {{ str_replace('App\\Mail\\', '', $fjob->mailable_class) }}
                                            @else
                                                {{ str_replace('Illuminate\\Mail\\', '', $fjob->display_name) }}
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if(isset($fjob->recipient))
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-900/30 text-blue-300 border border-blue-800">
                                                {{ $fjob->recipient }}
                                            </span>
                                        @else
                                            <span class="text-slate-600 italic text-sm">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-400 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($fjob->failed_at)->diffForHumans() }}
                                    </td>
                                    <td class="px-6 py-4 text-xs font-mono max-w-[200px] truncate text-red-400" title="{{ $fjob->exception }}">
                                        {{ $fjob->exception }}
                                    </td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap text-sm font-semibold space-x-3">
                                        <button wire:click="showException({{ $fjob->id }})" class="text-indigo-400 hover:text-indigo-300 transition-colors">
                                            View Error
                                        </button>
                                        <button wire:click="retryJob('{{ $fjob->uuid }}')" class="text-green-400 hover:text-green-300 transition-colors">
                                            Retry
                                        </button>
                                        <button wire:click="deleteFailedJob({{ $fjob->id }})" wire:confirm="Delete this failed record?" class="text-red-400 hover:text-red-300 transition-colors">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-12 h-12 mb-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span class="text-lg">No failed emails recorded in history.</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($failedJobs->hasPages())
                    <div class="px-6 py-4 bg-slate-800/20 border-t border-slate-800">
                        {{ $failedJobs->links(data: ['pageName' => 'failedPage']) }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Exception Inspection Modal -->
        @if($selectedException)
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="modal-title">
                <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-4xl shadow-2xl flex flex-col max-h-[85vh]">
                    <!-- Header -->
                    <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center bg-slate-900/50 rounded-t-3xl">
                        <h3 id="modal-title" class="text-lg font-bold text-white flex items-center">
                            <span class="inline-block w-2.5 h-2.5 bg-red-500 rounded-full mr-2.5 animate-pulse"></span>
                            Job Failure Exception Details
                        </h3>
                        <button wire:click="closeExceptionModal" class="text-slate-400 hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <!-- Content -->
                    <div class="p-6 overflow-y-auto text-slate-300 font-mono text-sm leading-relaxed flex-1 space-y-4">
                        <div class="bg-slate-950 p-4 rounded-xl border border-slate-850">
                            <span class="text-xs text-slate-500 block uppercase tracking-wider mb-1 font-sans">Failed Job UUID</span>
                            <span class="text-red-400 font-bold select-all">{{ $selectedException['uuid'] }}</span>
                        </div>
                        <div class="bg-slate-950 p-4 rounded-xl border border-slate-850">
                            <span class="text-xs text-slate-500 block uppercase tracking-wider mb-2 font-sans font-semibold">Exception Stack Trace</span>
                            <pre class="text-slate-200 text-xs whitespace-pre-wrap select-text selection:bg-red-900/50 max-h-[40vh] overflow-y-auto overflow-x-auto p-2 bg-slate-900 rounded border border-slate-850">{{ $selectedException['exception'] }}</pre>
                        </div>
                    </div>
                    <!-- Footer -->
                    <div class="px-6 py-4 border-t border-slate-800 bg-slate-900/50 flex justify-end rounded-b-3xl">
                        <button wire:click="closeExceptionModal" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-bold uppercase tracking-wider transition-colors shadow">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        @endif
        
    </div>
</div>
