<div 
    x-data 
    x-on:open-feedback-modal.window="$wire.openModal($event.detail || {})"
>
    @if($isOpen)
        <div 
            class="fixed inset-0 z-[999] flex items-center justify-center p-4 sm:p-6 overflow-y-auto"
            aria-labelledby="feedback-modal-title" 
            role="dialog" 
            aria-modal="true"
        >
            <!-- Backdrop -->
            <div 
                wire:click="closeModal" 
                class="fixed inset-0 bg-slate-950/80 backdrop-blur-md transition-opacity"
            ></div>

            <!-- Modal Panel -->
            <div class="relative bg-slate-900 border border-slate-750 rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl z-10 text-white my-8 max-h-[90vh] overflow-y-auto">
                <!-- Close Button -->
                <button 
                    type="button" 
                    wire:click="closeModal" 
                    class="absolute top-5 right-5 text-slate-400 hover:text-white p-2 rounded-xl bg-slate-800/80 hover:bg-slate-700 transition"
                    title="Close"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>

                @if($submitted)
                    <!-- Success State -->
                    <div class="py-8 text-center space-y-4">
                        <div class="w-16 h-16 rounded-full bg-emerald-950/80 border border-emerald-500/50 text-emerald-400 flex items-center justify-center mx-auto text-3xl shadow-lg shadow-emerald-900/30 animate-bounce">
                            ✓
                        </div>
                        <h3 class="text-2xl font-black text-white">Thank You for Your Feedback!</h3>
                        <p class="text-slate-300 text-sm max-w-md mx-auto leading-relaxed">
                            Your submission has been dispatched directly to our community and admin inbox. We review all dark-sky suggestions and feature ideas carefully.
                        </p>

                        <!-- Discord Server Callout in Success State -->
                        <div class="mt-6 p-5 rounded-2xl bg-indigo-950/50 border border-indigo-500/30 text-left max-w-md mx-auto">
                            <div class="flex items-center gap-3 mb-2">
                                <svg class="w-6 h-6 text-indigo-400 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028c.462-.63.874-1.295 1.226-1.994.021-.041.001-.09-.041-.106a13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.929 1.793 8.18 1.793 12.061 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.893.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.028zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/>
                                </svg>
                                <span class="font-bold text-sm text-indigo-200">Join the Astronotify Discord</span>
                            </div>
                            <p class="text-xs text-indigo-300 mb-3">
                                Chat in real time with our astronomical community, share transit images, and receive immediate updates.
                            </p>
                            <div class="text-[11px] text-amber-300 bg-amber-950/50 border border-amber-500/30 rounded-lg p-2 mb-3">
                                ⚠️ <strong>Important:</strong> Please select a role upon joining the server to unlock channels.
                            </div>
                            <a 
                                href="{{ $discordInviteUrl }}" 
                                target="_blank" 
                                rel="noopener noreferrer"
                                class="inline-flex items-center justify-center gap-2 w-full px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs transition shadow-md"
                            >
                                Open Discord Server ↗
                            </a>
                        </div>

                        <div class="pt-4">
                            <button 
                                type="button" 
                                wire:click="closeModal" 
                                class="px-6 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold transition"
                            >
                                Done
                            </button>
                        </div>
                    </div>
                @else
                    <!-- Modal Header -->
                    <div class="mb-6">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-purple-950/70 border border-purple-500/40 text-purple-300 text-xs font-bold uppercase tracking-wider mb-2">
                            <span>🛰️</span>
                            <span>Community &amp; Support Hub</span>
                        </div>
                        <h2 id="feedback-modal-title" class="text-2xl font-black text-white">
                            Feedback &amp; Spot Requests
                        </h2>
                        <p class="text-slate-400 text-xs mt-1">
                            Suggest new dark-sky observation locations, propose feature enhancements, or report bugs.
                        </p>
                    </div>

                    <!-- Discord Server Banner -->
                    <div class="mb-6 p-3.5 rounded-2xl bg-indigo-950/40 border border-indigo-500/30 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-indigo-600/30 border border-indigo-500/40 flex items-center justify-center text-indigo-400 shrink-0">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028c.462-.63.874-1.295 1.226-1.994.021-.041.001-.09-.041-.106a13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.929 1.793 8.18 1.793 12.061 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.893.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.028zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/>
                                </svg>
                            </div>
                            <div class="text-xs">
                                <p class="text-indigo-200 font-bold">Have a quick question or live discussion?</p>
                                <p class="text-indigo-300 text-[11px]">Join our Discord community <span class="text-amber-300 font-medium">(Select a role upon joining to unlock channels)</span></p>
                            </div>
                        </div>
                        <a 
                            href="{{ $discordInviteUrl }}" 
                            target="_blank" 
                            rel="noopener noreferrer" 
                            class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold transition shrink-0 shadow"
                        >
                            <span>Join Discord</span>
                            <span>↗</span>
                        </a>
                    </div>

                    <!-- Type Selector Buttons -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-6">
                        <button 
                            type="button" 
                            wire:click="$set('type', 'spot_suggestion')" 
                            class="px-3 py-2.5 rounded-xl border text-xs font-bold transition flex items-center justify-center gap-1.5 {{ $type === 'spot_suggestion' ? 'bg-emerald-600 text-white border-emerald-500 shadow-md' : 'bg-slate-800/80 text-slate-300 border-slate-700 hover:bg-slate-800 hover:text-white' }}"
                        >
                            <span>🔭</span>
                            <span>Suggest Spot</span>
                        </button>
                        <button 
                            type="button" 
                            wire:click="$set('type', 'feature_request')" 
                            class="px-3 py-2.5 rounded-xl border text-xs font-bold transition flex items-center justify-center gap-1.5 {{ $type === 'feature_request' ? 'bg-purple-600 text-white border-purple-500 shadow-md' : 'bg-slate-800/80 text-slate-300 border-slate-700 hover:bg-slate-800 hover:text-white' }}"
                        >
                            <span>💡</span>
                            <span>Feature</span>
                        </button>
                        <button 
                            type="button" 
                            wire:click="$set('type', 'bug_report')" 
                            class="px-3 py-2.5 rounded-xl border text-xs font-bold transition flex items-center justify-center gap-1.5 {{ $type === 'bug_report' ? 'bg-rose-600 text-white border-rose-500 shadow-md' : 'bg-slate-800/80 text-slate-300 border-slate-700 hover:bg-slate-800 hover:text-white' }}"
                        >
                            <span>🐛</span>
                            <span>Bug Report</span>
                        </button>
                        <button 
                            type="button" 
                            wire:click="$set('type', 'general')" 
                            class="px-3 py-2.5 rounded-xl border text-xs font-bold transition flex items-center justify-center gap-1.5 {{ $type === 'general' ? 'bg-blue-600 text-white border-blue-500 shadow-md' : 'bg-slate-800/80 text-slate-300 border-slate-700 hover:bg-slate-800 hover:text-white' }}"
                        >
                            <span>📬</span>
                            <span>General</span>
                        </button>
                    </div>

                    <!-- Submission Form -->
                    <form wire:submit="submit" class="space-y-4">
                        <!-- Honeypot -->
                        <div class="hidden" aria-hidden="true">
                            <label for="feedback_website">Leave this field blank</label>
                            <input type="text" id="feedback_website" wire:model="website" tabindex="-1" autocomplete="off" />
                        </div>

                        <!-- Name & Email -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Your Name *</label>
                                <input 
                                    type="text" 
                                    wire:model="name" 
                                    class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-3.5 py-2 text-sm text-white focus:outline-none focus:border-purple-500 transition" 
                                    placeholder="Jane Doe"
                                    required
                                >
                                @error('name') <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Email Address *</label>
                                <input 
                                    type="email" 
                                    wire:model="email" 
                                    class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-3.5 py-2 text-sm text-white focus:outline-none focus:border-purple-500 transition" 
                                    placeholder="jane@example.com"
                                    required
                                >
                                @error('email') <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Title / Spot Name -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                                {{ $type === 'spot_suggestion' ? 'Stargazing Spot Name *' : 'Title / Subject *' }}
                            </label>
                            <input 
                                type="text" 
                                wire:model="title" 
                                class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-3.5 py-2 text-sm text-white focus:outline-none focus:border-purple-500 transition" 
                                placeholder="{{ $type === 'spot_suggestion' ? 'e.g., Kielder Forest Sky Park or Pic du Midi' : 'Brief summary of your request or issue' }}"
                                required
                            >
                            @error('title') <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Spot-specific Details -->
                        @if($type === 'spot_suggestion')
                            <div class="p-4 rounded-2xl bg-slate-950/70 border border-slate-800 space-y-3">
                                <h4 class="text-xs font-black uppercase tracking-wider text-emerald-400 flex items-center gap-1.5">
                                    <span>📍</span>
                                    <span>Geographic &amp; Dark Sky Specifications</span>
                                </h4>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-300 mb-1">Country *</label>
                                        <input 
                                            type="text" 
                                            wire:model="country" 
                                            placeholder="United Kingdom, Spain, USA..." 
                                            class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-1.5 text-xs text-white focus:outline-none focus:border-emerald-500 transition"
                                        >
                                        @error('country') <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-300 mb-1">Region / Province / County</label>
                                        <input 
                                            type="text" 
                                            wire:model="region" 
                                            placeholder="Northumberland, Andalusia, Utah..." 
                                            class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-1.5 text-xs text-white focus:outline-none focus:border-emerald-500 transition"
                                        >
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-300 mb-1">Latitude (°)</label>
                                        <input 
                                            type="number" 
                                            step="0.0001" 
                                            wire:model="latitude" 
                                            placeholder="e.g. 55.2333" 
                                            class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-1.5 text-xs text-white focus:outline-none focus:border-emerald-500 transition"
                                        >
                                        @error('latitude') <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-300 mb-1">Longitude (°)</label>
                                        <input 
                                            type="number" 
                                            step="0.0001" 
                                            wire:model="longitude" 
                                            placeholder="e.g. -2.5833" 
                                            class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-1.5 text-xs text-white focus:outline-none focus:border-emerald-500 transition"
                                        >
                                        @error('longitude') <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-300 mb-1">Elevation (meters)</label>
                                        <input 
                                            type="number" 
                                            wire:model="elevation" 
                                            placeholder="350" 
                                            class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-1.5 text-xs text-white focus:outline-none focus:border-emerald-500 transition"
                                        >
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-300 mb-1">Estimated Bortle Rating</label>
                                        <select 
                                            wire:model="bortle_class" 
                                            class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-1.5 text-xs text-white focus:outline-none focus:border-emerald-500 transition"
                                        >
                                            <option value="1">Class 1: Excellent truly dark sky</option>
                                            <option value="2">Class 2: Truly dark site</option>
                                            <option value="3">Class 3: Rural sky</option>
                                            <option value="4">Class 4: Rural/suburban transition</option>
                                            <option value="5">Class 5: Suburban sky</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-300 mb-1">Dark Sky Designation</label>
                                        <input 
                                            type="text" 
                                            wire:model="dark_sky_status" 
                                            placeholder="IDA International Dark Sky Park, Reserve, etc." 
                                            class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-1.5 text-xs text-white focus:outline-none focus:border-emerald-500 transition"
                                        >
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Description / Details -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                                {{ $type === 'spot_suggestion' ? 'Location Description & Observing Tips *' : 'Description / Details *' }}
                            </label>
                            <textarea 
                                wire:model="description" 
                                rows="4" 
                                class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-sm text-white focus:outline-none focus:border-purple-500 transition placeholder-slate-500" 
                                placeholder="{{ $type === 'spot_suggestion' ? 'Tell us why this location is great for stargazing, parking/access conditions, horizon obstructions, or public access notes...' : 'Please describe your request or issue with as much detail as possible...' }}"
                                required
                            ></textarea>
                            @error('description') <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-between pt-2">
                            <button 
                                type="button" 
                                wire:click="closeModal" 
                                class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-bold transition"
                            >
                                Cancel
                            </button>

                            <button 
                                type="submit" 
                                class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-500 hover:to-blue-500 text-white text-xs font-bold transition shadow-lg flex items-center gap-2"
                            >
                                <span wire:loading.remove wire:target="submit">Submit Feedback</span>
                                <span wire:loading wire:target="submit" class="flex items-center gap-1.5">
                                    <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
                                    <span>Sending...</span>
                                </span>
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    @endif
</div>
