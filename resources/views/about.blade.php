<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-gray-200 leading-tight">
            {{ __('About Astronotify') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-slate-950 min-h-screen text-white">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-slate-800 border border-slate-700 overflow-hidden shadow-xl sm:rounded-3xl p-8">
                <h3 class="text-3xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-500 mb-6">
                    What is Astronotify?
                </h3>
                
                <div class="prose prose-invert max-w-none text-slate-300">
                    <p class="text-lg leading-relaxed mb-4">
                        Astronotify is your personal automated stargazing assistant. We continuously monitor the skies so you don't have to.
                    </p>
                    
                    <h4 class="text-xl font-semibold text-purple-300 mt-8 mb-4">How it works:</h4>
                    <ul class="list-disc pl-5 space-y-2 mb-8">
                        <li>Register your favorite dark sky locations using precise coordinates.</li>
                        <li>Set your minimum acceptable requirements based on night length, max cloud-cover, and maximum wind speed.</li>
                        <li>Astronotify's background workers analyze 8 days of highly accurate meteorological data every single day to map alignments.</li>
                        <li>You get an automated email exactly when perfect conditions are locked into the forecast!</li>
                    </ul>

                    <div class="p-4 mt-12 bg-slate-900 border border-slate-700 inline-block rounded-xl">
                        <p class="text-sm italic text-slate-400">
                            (This is a placeholder page! You can edit the structure and content inside <code>resources/views/about.blade.php</code>)
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
