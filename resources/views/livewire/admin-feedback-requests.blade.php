<div class="py-12 bg-slate-950 flex-grow text-white min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-purple-950/70 border border-purple-500/40 text-purple-300 text-xs font-bold uppercase tracking-wider mb-2">
                    <span>📬</span>
                    <span>Inbox &amp; Community Dispatch</span>
                </div>
                <h1 class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-white via-slate-100 to-purple-200">
                    Feedback &amp; Spot Requests
                </h1>
                <p class="text-slate-400 text-xs mt-1">
                    Review incoming dark-sky location suggestions, user bug reports, and community feature ideas.
                </p>
            </div>

            <!-- Discord Server Link -->
            <div class="flex items-center gap-3">
                <a 
                    href="https://discord.gg/UuwaXjRjZU" 
                    target="_blank" 
                    rel="noopener noreferrer"
                    class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-lg transition flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028c.462-.63.874-1.295 1.226-1.994.021-.041.001-.09-.041-.106a13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.929 1.793 8.18 1.793 12.061 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.893.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.028zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/>
                    </svg>
                    <span>Discord Server ↗</span>
                </a>
            </div>
        </div>

        <!-- Metric Badges -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 shadow-xl">
                <span class="block text-2xl font-black text-amber-400">{{ $counts['pending'] }}</span>
                <span class="text-[11px] text-slate-400 uppercase tracking-wider font-semibold">Pending Review</span>
            </div>
            <div class="p-4 rounded-2xl bg-emerald-950/40 border border-emerald-500/30 shadow-xl">
                <span class="block text-2xl font-black text-emerald-400">{{ $counts['spots'] }}</span>
                <span class="text-[11px] text-emerald-300 uppercase tracking-wider font-semibold">Spot Suggestions</span>
            </div>
            <div class="p-4 rounded-2xl bg-purple-950/40 border border-purple-500/30 shadow-xl">
                <span class="block text-2xl font-black text-purple-400">{{ $counts['features'] }}</span>
                <span class="text-[11px] text-purple-300 uppercase tracking-wider font-semibold">Feature Ideas</span>
            </div>
            <div class="p-4 rounded-2xl bg-rose-950/40 border border-rose-500/30 shadow-xl">
                <span class="block text-2xl font-black text-rose-400">{{ $counts['bugs'] }}</span>
                <span class="text-[11px] text-rose-300 uppercase tracking-wider font-semibold">Bug Reports</span>
            </div>
        </div>

        <!-- Session Message -->
        @if (session()->has('message'))
            <div class="p-4 rounded-xl bg-emerald-500/20 border border-emerald-500/50 text-emerald-300 text-xs font-semibold">
                {{ session('message') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="p-4 rounded-xl bg-rose-500/20 border border-rose-500/50 text-rose-300 text-xs font-semibold">
                {{ session('error') }}
            </div>
        @endif

        <!-- Filter Controls -->
        <div class="p-4 rounded-2xl bg-slate-900/90 border border-slate-800 shadow-xl flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4">
            <div class="flex-1 max-w-md">
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Search by title, submitter, email, or content..." 
                    class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-purple-500 transition"
                />
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <!-- Status Filter -->
                <select 
                    wire:model.live="statusFilter" 
                    class="bg-slate-950 border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-purple-500 transition cursor-pointer font-medium"
                >
                    <option value="pending">Status: Pending Only</option>
                    <option value="approved">Status: Approved</option>
                    <option value="resolved">Status: Resolved</option>
                    <option value="rejected">Status: Rejected</option>
                    <option value="all">Status: All Statuses</option>
                </select>

                <!-- Type Filter -->
                <select 
                    wire:model.live="typeFilter" 
                    class="bg-slate-950 border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-purple-500 transition cursor-pointer font-medium"
                >
                    <option value="all">Category: All Types</option>
                    <option value="spot_suggestion">Category: Spot Suggestions</option>
                    <option value="feature_request">Category: Feature Requests</option>
                    <option value="bug_report">Category: Bug Reports</option>
                    <option value="general">Category: General Feedback</option>
                </select>
            </div>
        </div>

        <!-- Submissions Table -->
        <div class="bg-slate-900/80 border border-slate-800 rounded-3xl overflow-hidden shadow-2xl">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-800 text-left text-xs">
                    <thead class="bg-slate-950/60 uppercase tracking-wider text-slate-400 font-bold">
                        <tr>
                            <th class="px-6 py-4">Category</th>
                            <th class="px-6 py-4">Title / Location</th>
                            <th class="px-6 py-4">Submitter</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Date</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 font-medium">
                        @forelse($submissions as $sub)
                            <tr class="hover:bg-slate-800/40 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 rounded-xl text-[11px] font-bold {{ $sub->getTypeBadgeClass() }}">
                                        {{ $sub->getTypeLabel() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 max-w-xs">
                                    <div class="font-bold text-white truncate cursor-pointer hover:text-purple-300" wire:click="viewSubmission({{ $sub->id }})">
                                        {{ $sub->title }}
                                    </div>
                                    @if($sub->type === 'spot_suggestion' && is_array($sub->meta))
                                        <div class="text-[11px] text-slate-400 mt-0.5 truncate">
                                            📍 {{ $sub->meta['country'] ?? '' }}
                                            @if(!empty($sub->meta['region']))
                                                ({{ $sub->meta['region'] }})
                                            @endif
                                            @if(!empty($sub->meta['bortle_class']))
                                                • Bortle {{ $sub->meta['bortle_class'] }}
                                            @endif
                                        </div>
                                    @else
                                        <div class="text-[11px] text-slate-400 mt-0.5 truncate">
                                            {{ Str::limit($sub->description, 60) }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-white font-semibold">{{ $sub->name }}</div>
                                    <a href="mailto:{{ $sub->email }}" class="text-[11px] text-purple-400 hover:underline">{{ $sub->email }}</a>
                                    @if($sub->user_id)
                                        <span class="text-[10px] text-emerald-400 block font-bold">Registered User</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 rounded-xl text-[11px] font-bold {{ $sub->getStatusBadgeClass() }}">
                                        {{ ucfirst($sub->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-400">
                                    {{ $sub->created_at->format('M d, Y') }}
                                    <span class="text-[10px] block text-slate-500">{{ $sub->created_at->diffForHumans() }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                                    @if($sub->type === 'spot_suggestion' && $sub->status !== 'approved')
                                        <button 
                                            type="button" 
                                            wire:click="promoteToSpot({{ $sub->id }})" 
                                            class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition shadow"
                                            title="Promote directly to live Stargazing Spot"
                                        >
                                            Promote to Spot ↗
                                        </button>
                                    @endif

                                    <button 
                                        type="button" 
                                        wire:click="viewSubmission({{ $sub->id }})" 
                                        class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs transition"
                                    >
                                        Review
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                    <p class="text-lg font-bold text-slate-300">No submissions found</p>
                                    <p class="text-xs text-slate-500 mt-1">There are no feedback requests matching your active filters.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-800">
                {{ $submissions->links() }}
            </div>
        </div>
    </div>

    <!-- Review / Details Modal -->
    @if($viewingSubmission)
        <div class="fixed inset-0 z-[999] flex items-center justify-center p-4 sm:p-6 overflow-y-auto" role="dialog" aria-modal="true">
            <div wire:click="closeDetailsModal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-md"></div>

            <div class="relative bg-slate-900 border border-slate-750 rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl z-10 text-white max-h-[90vh] overflow-y-auto space-y-6">
                <!-- Modal Top -->
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="px-2.5 py-1 rounded-xl text-[11px] font-bold {{ $viewingSubmission->getTypeBadgeClass() }}">
                                {{ $viewingSubmission->getTypeLabel() }}
                            </span>
                            <span class="px-2.5 py-1 rounded-xl text-[11px] font-bold {{ $viewingSubmission->getStatusBadgeClass() }}">
                                {{ ucfirst($viewingSubmission->status) }}
                            </span>
                        </div>
                        <h2 class="text-2xl font-black text-white">
                            {{ $viewingSubmission->title }}
                        </h2>
                        <div class="text-xs text-slate-400 mt-1">
                            Submitted by <strong class="text-white">{{ $viewingSubmission->name }}</strong> (<a href="mailto:{{ $viewingSubmission->email }}" class="text-purple-400 hover:underline">{{ $viewingSubmission->email }}</a>) on {{ $viewingSubmission->created_at->format('M d, Y H:i') }}
                        </div>
                    </div>

                    <button 
                        type="button" 
                        wire:click="closeDetailsModal" 
                        class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition"
                    >
                        ✕
                    </button>
                </div>

                <!-- Spot Specifications (if applicable) -->
                @if($viewingSubmission->type === 'spot_suggestion' && is_array($viewingSubmission->meta))
                    <div class="p-4 rounded-2xl bg-slate-950/70 border border-slate-800 space-y-2 text-xs">
                        <h4 class="font-bold text-emerald-400 uppercase tracking-wider text-[11px]">Suggested Spot Details</h4>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-slate-300">
                            <div><strong class="text-slate-400 block text-[10px]">Country:</strong> {{ $viewingSubmission->meta['country'] ?? 'N/A' }}</div>
                            <div><strong class="text-slate-400 block text-[10px]">Region:</strong> {{ $viewingSubmission->meta['region'] ?? 'N/A' }}</div>
                            <div><strong class="text-slate-400 block text-[10px]">Bortle Scale:</strong> Class {{ $viewingSubmission->meta['bortle_class'] ?? 'N/A' }}</div>
                            <div><strong class="text-slate-400 block text-[10px]">Latitude:</strong> {{ $viewingSubmission->meta['latitude'] ?? 'N/A' }}°</div>
                            <div><strong class="text-slate-400 block text-[10px]">Longitude:</strong> {{ $viewingSubmission->meta['longitude'] ?? 'N/A' }}°</div>
                            <div><strong class="text-slate-400 block text-[10px]">Elevation:</strong> {{ $viewingSubmission->meta['elevation'] ?? 0 }}m</div>
                        </div>
                        @if(!empty($viewingSubmission->meta['dark_sky_status']))
                            <div class="pt-1">
                                <strong class="text-slate-400 block text-[10px]">Designation:</strong>
                                <span>{{ $viewingSubmission->meta['dark_sky_status'] }}</span>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Diagnostics (if bug report) -->
                @if($viewingSubmission->type === 'bug_report' && is_array($viewingSubmission->meta))
                    <div class="p-4 rounded-2xl bg-slate-950/70 border border-slate-800 space-y-2 text-xs">
                        <h4 class="font-bold text-rose-400 uppercase tracking-wider text-[11px]">Client Diagnostics</h4>
                        @if(!empty($viewingSubmission->meta['url']))
                            <div><strong class="text-slate-400 block text-[10px]">Page URL:</strong> <a href="{{ $viewingSubmission->meta['url'] }}" target="_blank" class="text-purple-400 hover:underline">{{ $viewingSubmission->meta['url'] }}</a></div>
                        @endif
                        @if(!empty($viewingSubmission->meta['user_agent']))
                            <div><strong class="text-slate-400 block text-[10px]">User Agent:</strong> <code class="text-[11px] text-slate-300">{{ $viewingSubmission->meta['user_agent'] }}</code></div>
                        @endif
                    </div>
                @endif

                <!-- Full Description -->
                <div>
                    <h4 class="font-bold text-slate-300 text-xs uppercase tracking-wider mb-2">Detailed Notes / Description</h4>
                    <div class="p-4 rounded-2xl bg-slate-950/90 border border-slate-800 text-xs sm:text-sm text-slate-200 leading-relaxed whitespace-pre-wrap">
                        {{ $viewingSubmission->description }}
                    </div>
                </div>

                <!-- Admin Notes -->
                <div>
                    <label class="block font-bold text-slate-300 text-xs uppercase tracking-wider mb-2">Administrative Review Notes</label>
                    <textarea 
                        wire:model="editingAdminNotes" 
                        rows="3" 
                        class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-purple-500 transition" 
                        placeholder="Internal team notes, research findings, or resolution details..."
                    ></textarea>
                    <div class="flex justify-end mt-2">
                        <button 
                            type="button" 
                            wire:click="saveAdminNotes" 
                            class="px-3.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs transition"
                        >
                            Save Notes
                        </button>
                    </div>
                </div>

                <!-- Action Controls & Status Changer -->
                <div class="pt-4 border-t border-slate-800 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-slate-400 font-semibold">Change Status:</span>
                        <button 
                            type="button" 
                            wire:click="updateStatus({{ $viewingSubmission->id }}, 'pending')" 
                            class="px-2.5 py-1 rounded-lg text-xs font-bold {{ $viewingSubmission->status === 'pending' ? 'bg-amber-600 text-white' : 'bg-slate-800 text-slate-400 hover:text-white' }}"
                        >
                            Pending
                        </button>
                        <button 
                            type="button" 
                            wire:click="updateStatus({{ $viewingSubmission->id }}, 'approved')" 
                            class="px-2.5 py-1 rounded-lg text-xs font-bold {{ $viewingSubmission->status === 'approved' ? 'bg-emerald-600 text-white' : 'bg-slate-800 text-slate-400 hover:text-white' }}"
                        >
                            Approve
                        </button>
                        <button 
                            type="button" 
                            wire:click="updateStatus({{ $viewingSubmission->id }}, 'resolved')" 
                            class="px-2.5 py-1 rounded-lg text-xs font-bold {{ $viewingSubmission->status === 'resolved' ? 'bg-teal-600 text-white' : 'bg-slate-800 text-slate-400 hover:text-white' }}"
                        >
                            Resolve
                        </button>
                        <button 
                            type="button" 
                            wire:click="updateStatus({{ $viewingSubmission->id }}, 'rejected')" 
                            class="px-2.5 py-1 rounded-lg text-xs font-bold {{ $viewingSubmission->status === 'rejected' ? 'bg-rose-600 text-white' : 'bg-slate-800 text-slate-400 hover:text-white' }}"
                        >
                            Reject
                        </button>
                    </div>

                    <div class="flex items-center gap-2">
                        @if($viewingSubmission->type === 'spot_suggestion' && $viewingSubmission->status !== 'approved')
                            <button 
                                type="button" 
                                wire:click="promoteToSpot({{ $viewingSubmission->id }})" 
                                class="px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white text-xs font-bold transition shadow-lg flex items-center gap-1.5"
                            >
                                <span>Promote to Live Spot</span>
                                <span>↗</span>
                            </button>
                        @endif

                        <button 
                            type="button" 
                            wire:click="deleteSubmission({{ $viewingSubmission->id }})" 
                            wire:confirm="Are you sure you want to delete this submission?"
                            class="px-3 py-2 rounded-xl bg-rose-950/80 hover:bg-rose-900 border border-rose-500/40 text-rose-300 hover:text-white text-xs font-bold transition"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
