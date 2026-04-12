<x-slot name="header">
    <h2 class="font-semibold text-xl text-slate-800 dark:text-gray-200 leading-tight">
        {{ __('Admin Dashboard') }}
    </h2>
</x-slot>

<div class="py-12 bg-slate-950 text-white min-h-screen">
    <div class="max-w-8xl mx-auto sm:px-6 lg:px-8 space-y-8">
        
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
            <div class="bg-slate-800 border border-slate-700 rounded-2xl p-6 shadow-lg md:col-span-2">
                <h3 class="text-slate-400 text-sm font-semibold uppercase tracking-wider">Optimal Forecasts Historical</h3>
                <p class="text-4xl font-extrabold text-emerald-400 mt-2">{{ $optimalConditions }} <span class="text-lg text-slate-500">/ {{ $totalConditions }} total mapped</span></p>
                <p class="text-xs text-slate-500 mt-1">Spanning across {{ $uniqueSearchedLocations }} independent location profiles</p>
            </div>
        </div>

        <!-- Registration Chart -->
        <div class="bg-slate-800/50 border border-slate-700 rounded-3xl p-6 shadow-xl">
            <h2 class="text-2xl font-bold text-white mb-6">Registrations (Last 7 Days)</h2>
            <div class="relative h-96 w-full">
                <canvas id="registrationChart"></canvas>
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
