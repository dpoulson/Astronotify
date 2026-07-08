<x-slot name="header">
    <h2 class="font-semibold text-xl text-slate-800 dark:text-gray-200 leading-tight">
        {{ __('Admin Dashboard') }}
    </h2>
</x-slot>

<div class="py-12 bg-slate-950 text-white flex-grow">
    <div class="max-w-8xl mx-auto sm:px-6 lg:px-8 space-y-8">
        @if($failedEmailCount > 0)
            <div class="bg-red-950/40 border border-red-800 rounded-3xl p-6 flex flex-col md:flex-row items-center justify-between gap-4 shadow-xl backdrop-blur-xl">
                <div class="flex items-center space-x-4">
                    <span class="text-3xl animate-bounce">⚠️</span>
                    <div>
                        <h3 class="text-lg font-bold text-red-300">Outbound Email Failures Detected</h3>
                        <p class="text-sm text-slate-400">There are <span class="text-white font-bold">{{ $failedEmailCount }}</span> failed email jobs in the queue. This might indicate SMTP connection issues.</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3 w-full md:w-auto shrink-0">
                    <button wire:click="retryAllFailedEmails" class="w-full md:w-auto bg-green-750 hover:bg-green-700 text-white font-semibold py-2.5 px-4 rounded-xl shadow transition-colors text-xs uppercase tracking-wider">
                        Retry All Failed
                    </button>
                    <a href="{{ route('admin.queue') }}" class="w-full md:w-auto bg-slate-805 hover:bg-slate-800 border border-slate-700 text-slate-200 font-semibold py-2.5 px-4 rounded-xl shadow transition-colors text-xs uppercase tracking-wider text-center">
                        Inspect Queue &rarr;
                    </a>
                </div>
            </div>
        @endif

        <!-- Stats Row -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-slate-800 border border-slate-700 rounded-2xl p-6 shadow-lg flex flex-col justify-between">
                <div>
                    <h3 class="text-slate-400 text-sm font-semibold uppercase tracking-wider">Total Users</h3>
                    <p class="text-4xl font-extrabold text-blue-400 mt-2">{{ $totalUsers }}</p>
                </div>
                <a href="{{ route('admin.users') }}" class="mt-4 text-sm text-blue-300 hover:text-blue-200 transition-colors font-semibold">View All Users &rarr;</a>
            </div>
            <div class="bg-slate-800 border border-slate-700 rounded-2xl p-6 shadow-lg flex flex-col justify-between">
                <div>
                    <h3 class="text-slate-400 text-sm font-semibold uppercase tracking-wider">Total Locations</h3>
                    <p class="text-4xl font-extrabold text-purple-400 mt-2">{{ $totalLocations }}</p>
                </div>
                <a href="{{ route('admin.locations') }}" class="mt-4 text-sm text-purple-300 hover:text-purple-200 transition-colors font-semibold">View All Locations &rarr;</a>
            </div>
            <div class="bg-slate-800 border border-slate-700 rounded-2xl p-6 shadow-lg flex flex-col justify-between">
                <div>
                    <h3 class="text-slate-400 text-sm font-semibold uppercase tracking-wider">Weather API Calls</h3>
                    <div class="mt-4 space-y-4">
                        <div class="flex justify-between items-end">
                            <span class="text-xs text-slate-500 font-medium uppercase tracking-tight">Today</span>
                            <span class="text-3xl font-extrabold text-yellow-400 leading-none">{{ $apiCallsToday }}</span>
                        </div>
                        <div class="flex justify-between items-end border-t border-slate-700/50 pt-2">
                            <span class="text-xs text-slate-500 font-medium uppercase tracking-tight">Last 7d</span>
                            <span class="text-lg font-bold text-slate-300 leading-none">{{ $apiCallsWeek }}</span>
                        </div>
                        <div class="flex justify-between items-end">
                            <span class="text-xs text-slate-500 font-medium uppercase tracking-tight">Last 30d</span>
                            <span class="text-lg font-bold text-slate-300 leading-none">{{ $apiCallsMonth }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-slate-800 border border-slate-700 rounded-2xl p-6 shadow-lg md:col-span-1">
                <h3 class="text-slate-400 text-sm font-semibold uppercase tracking-wider">Optimal Forecasts Historical</h3>
                <p class="text-3xl font-extrabold text-emerald-400 mt-2">{{ $optimalConditions }} <span class="text-lg text-slate-500">/ {{ $totalConditions }}</span></p>
                <p class="text-xs text-slate-500 mt-1">Spanning across {{ $uniqueSearchedLocations }} profiles</p>
            </div>
        </div>

        <!-- Registration Chart -->
        <div class="bg-slate-800/50 border border-slate-700 rounded-3xl p-6 shadow-xl">
            <h2 class="text-2xl font-bold text-white mb-6">Registrations (Last 7 Days)</h2>
            <div class="relative h-96 w-full">
                <canvas id="registrationChart"></canvas>
            </div>
        </div>

        <!-- System Operations & API Usage Tracker -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-slate-800/50 border border-slate-700 rounded-3xl p-6 shadow-xl space-y-4">
                <h2 class="text-xl font-bold text-white flex items-center">
                    <span class="mr-2">⚙️</span> System Operations
                </h2>
                <p class="text-sm text-slate-400">Trigger manual updates of the weather forecast data and ISS transit calculations. These operations usually run automatically via the daily scheduler.</p>
                
                <div class="flex flex-col sm:flex-row gap-3 pt-2">
                    <button 
                        wire:click="triggerWeatherFetch" 
                        wire:loading.attr="disabled" 
                        class="bg-blue-600 hover:bg-blue-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold py-3 px-5 rounded-xl shadow transition-all duration-200 flex items-center justify-center space-x-2 w-full sm:w-auto"
                    >
                        <span wire:loading.remove wire:target="triggerWeatherFetch">🌤️ Run Weather Fetch</span>
                        <span wire:loading wire:target="triggerWeatherFetch" class="inline-flex items-center space-x-2">
                            <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                            <span>Fetching...</span>
                        </span>
                    </button>

                    <button 
                        wire:click="triggerTransitCalculation" 
                        wire:loading.attr="disabled" 
                        class="bg-purple-600 hover:bg-purple-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold py-3 px-5 rounded-xl shadow transition-all duration-200 flex items-center justify-center space-x-2 w-full sm:w-auto"
                    >
                        <span wire:loading.remove wire:target="triggerTransitCalculation">🛰️ Run ISS Calculations</span>
                        <span wire:loading wire:target="triggerTransitCalculation" class="inline-flex items-center space-x-2">
                            <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                            <span>Propagating...</span>
                        </span>
                    </button>
                </div>

                @if($sysMessage)
                    <div class="mt-4 p-4 rounded-xl text-sm font-semibold {{ $sysMessageType === 'success' ? 'bg-green-950/40 text-green-300 border border-green-800' : 'bg-red-950/40 text-red-300 border border-red-800' }}">
                        {{ $sysMessage }}
                    </div>
                @endif
            </div>

            <!-- API Status Tracker -->
            <div class="bg-slate-800/50 border border-slate-700 rounded-3xl p-6 shadow-xl space-y-4">
                <h2 class="text-xl font-bold text-white flex items-center">
                    <span class="mr-2">📈</span> API Usage Limits
                </h2>
                <p class="text-sm text-slate-400">Open-Meteo allows up to 10,000 API requests per day on their free tier. Keep track of daily calls to verify scheduling limits.</p>
                
                <div class="space-y-4 pt-2">
                    <div>
                        <div class="flex justify-between text-xs font-semibold text-slate-400 mb-1.5">
                            <span>DAILY API CALLS</span>
                            <span class="font-mono">{{ $apiCallsToday }} / 10,000 requests</span>
                        </div>
                        <div class="w-full bg-slate-900 rounded-full h-2.5 overflow-hidden">
                            <div class="bg-gradient-to-r from-blue-500 to-purple-500 h-2.5 rounded-full" style="width: {{ min(100, ($apiCallsToday / 10000) * 100) }}%"></div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 text-xs">
                        <div class="bg-slate-900/50 border border-slate-700/50 p-3 rounded-xl">
                            <span class="block text-slate-500 font-medium">THIS WEEK</span>
                            <span class="text-lg font-bold text-slate-200 mt-1 block">{{ $apiCallsWeek }} requests</span>
                        </div>
                        <div class="bg-slate-900/50 border border-slate-700/50 p-3 rounded-xl">
                            <span class="block text-slate-500 font-medium">THIS MONTH</span>
                            <span class="text-lg font-bold text-slate-200 mt-1 block">{{ $apiCallsMonth }} requests</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('livewire:initialized', () => {
        const ctx = document.getElementById('registrationChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! $chartLabels !!},
                datasets: [
                    {
                        label: 'New Users',
                        data: {!! $chartUsers !!},
                        backgroundColor: 'rgba(59, 130, 246, 0.5)',
                        borderColor: 'rgb(59, 130, 246)',
                        borderWidth: 1,
                        borderRadius: 4
                    },
                    {
                        label: 'New Locations',
                        data: {!! $chartLocations !!},
                        backgroundColor: 'rgba(168, 85, 247, 0.5)',
                        borderColor: 'rgb(168, 85, 247)',
                        borderWidth: 1,
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { color: '#94a3b8', stepSize: 1 },
                        grid: { color: 'rgba(51, 65, 85, 0.5)' }
                    },
                    x: {
                        ticks: { color: '#94a3b8' },
                        grid: { display: false }
                    }
                },
                plugins: {
                    legend: {
                        labels: { color: '#cbd5e1' }
                    }
                }
            }
        });
    });
</script>
