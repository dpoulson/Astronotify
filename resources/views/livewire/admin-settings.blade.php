<div class="py-12 bg-slate-950 flex-grow text-white">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-slate-800/50 border border-slate-700 rounded-3xl p-6 shadow-xl">
            <h2 class="text-3xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-500 mb-6 flex items-center">
                System Logic Parameters
            </h2>

            @if (session()->has('message'))
                <div class="mb-4 bg-green-500/20 border border-green-500/50 text-green-300 p-4 rounded-xl backdrop-blur-md">
                    {{ session('message') }}
                </div>
            @endif

            <form wire:submit="save" class="space-y-6">
                <div class="p-6 bg-slate-900/60 rounded-2xl border border-slate-700">
                    <h3 class="text-xl font-semibold mb-4 text-slate-200">Cron Configurations</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2" title="How many days ahead the Open-Meteo API should fetch?">Forecast Window (Days)</label>
                            <input type="number" wire:model="forecast_days" class="w-full bg-slate-800 border border-slate-600 rounded-xl text-white focus:ring-purple-500 focus:border-purple-500" min="1" max="16">
                            <p class="text-xs text-slate-400 mt-2">Maximum 16 days from Open-Meteo. Note: A 2-day buffer is reserved for overnight stargazing evaluation (effective maximum is 14 nights).</p>
                            @error('forecast_days') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2" title="Precision for geospatial bounds in the backend DB groupings">Coordinate Grouping (Decimals)</label>
                            <input type="number" wire:model="grouping_decimal_places" class="w-full bg-slate-800 border border-slate-600 rounded-xl text-white focus:ring-purple-500 focus:border-purple-500" min="0" max="4">
                            <p class="text-xs text-slate-400 mt-2">Setting 1 generates ~11km bounds. Setting 0 forces ~111km coarse limits.</p>
                            @error('grouping_decimal_places') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2" title="ISS closest approach angular separation threshold in degrees">Conjunction Threshold (°)</label>
                            <input type="number" step="0.01" wire:model="conjunction_threshold" class="w-full bg-slate-800 border border-slate-600 rounded-xl text-white focus:ring-purple-500 focus:border-purple-500" min="0.01" max="5.00">
                            <p class="text-xs text-slate-400 mt-2">Passes within this angular separation threshold of the Sun/Moon are captured as transits/conjunctions.</p>
                            @error('conjunction_threshold') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                {{-- Discord & Community Integration --}}
                <div class="p-6 bg-slate-900/60 rounded-2xl border border-slate-700 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-semibold text-slate-200 flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028c.462-.63.874-1.295 1.226-1.994.021-.041.001-.09-.041-.106a13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.929 1.793 8.18 1.793 12.061 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.893.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.028zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/>
                                </svg>
                                <span>Discord Community &amp; Webhook Integration</span>
                            </h3>
                            <p class="text-xs text-slate-400 mt-1">Broadcast new spot suggestions and support requests directly into your Discord staff/alerts channel.</p>
                        </div>
                    </div>

                    @if (session()->has('webhook_message'))
                        <div class="p-3 rounded-xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 text-xs font-semibold">
                            {{ session('webhook_message') }}
                        </div>
                    @endif

                    @if (session()->has('webhook_error'))
                        <div class="p-3 rounded-xl bg-rose-500/20 border border-rose-500/40 text-rose-300 text-xs font-semibold">
                            {{ session('webhook_error') }}
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Discord Feedback Webhook URL</label>
                            <input 
                                type="url" 
                                wire:model="discord_feedback_webhook_url" 
                                placeholder="https://discord.com/api/webhooks/..." 
                                class="w-full bg-slate-800 border border-slate-600 rounded-xl text-white text-xs focus:ring-purple-500 focus:border-purple-500 px-3.5 py-2"
                            >
                            <p class="text-xs text-slate-400 mt-2">Create an Incoming Webhook in Server Settings &gt; Integrations &gt; Webhooks.</p>
                            @error('discord_feedback_webhook_url') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Discord Server Invite URL</label>
                            <input 
                                type="url" 
                                wire:model="discord_invite_url" 
                                placeholder="https://discord.gg/..." 
                                class="w-full bg-slate-800 border border-slate-600 rounded-xl text-white text-xs focus:ring-purple-500 focus:border-purple-500 px-3.5 py-2"
                            >
                            <p class="text-xs text-slate-400 mt-2">Public community invite link displayed across website touchpoints and support modals.</p>
                            @error('discord_invite_url') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="pt-2">
                        <button 
                            type="button" 
                            wire:click="testDiscordWebhook" 
                            class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md transition flex items-center gap-1.5"
                        >
                            <span>Send Test Webhook</span>
                            <span>↗</span>
                        </button>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-500 hover:to-purple-500 text-white font-bold py-3 px-8 rounded-xl shadow-lg transition-transform transform hover:scale-105">
                        Save Configurations
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
