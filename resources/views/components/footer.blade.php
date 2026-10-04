<footer x-data class="bg-slate-950 border-t border-slate-800 py-6 relative z-10 w-full mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center text-slate-400 text-sm gap-4">
        <div class="flex flex-col sm:flex-row items-center space-y-1 sm:space-y-0 sm:space-x-3 text-center sm:text-left">
            <span>&copy; {{ date('Y') }} {{ config('app.name', 'Astronotify') }}.</span>
            <span>A project by <a href="https://we-make-things.co.uk/" target="_blank" rel="noopener noreferrer" class="text-purple-400 hover:text-purple-300 font-semibold transition-colors">We Make Things</a>.</span>
            <span class="hidden sm:inline text-slate-700">&bull;</span>
            <div class="flex items-center space-x-3 text-xs">
                <a href="{{ route('about') }}" class="text-slate-400 hover:text-slate-200 transition-colors">About</a>
                <span>&bull;</span>
                <a href="{{ route('faq') }}" class="text-slate-400 hover:text-slate-200 transition-colors">Help &amp; FAQ</a>
                <span>&bull;</span>
                <a href="{{ route('privacy') }}" class="text-slate-400 hover:text-purple-400 transition-colors">Privacy</a>
                <span>&bull;</span>
                <button 
                    type="button" 
                    @click="window.Livewire ? Livewire.dispatch('open-feedback-modal') : window.dispatchEvent(new CustomEvent('open-feedback-modal'))"
                    onclick="window.Livewire ? Livewire.dispatch('open-feedback-modal') : window.dispatchEvent(new CustomEvent('open-feedback-modal'))"
                    class="text-purple-400 hover:text-purple-300 transition-colors font-semibold cursor-pointer"
                >
                    Feedback &amp; Spot Requests
                </button>
            </div>
        </div>
        
        <div class="flex flex-wrap items-center gap-3">
            <a 
                href="https://discord.gg/UuwaXjRjZU" 
                target="_blank" 
                rel="noopener noreferrer"
                class="group flex items-center space-x-1.5 text-indigo-300 hover:text-white transition-colors bg-indigo-950/60 hover:bg-indigo-900/80 border border-indigo-500/40 px-3.5 py-1.5 rounded-full text-xs font-bold shadow-sm"
                title="Join our Discord community (Select a role upon joining)"
            >
                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                    <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028c.462-.63.874-1.295 1.226-1.994.021-.041.001-.09-.041-.106a13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.929 1.793 8.18 1.793 12.061 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.893.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.028zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/>
                </svg>
                <span>Discord</span>
            </a>

            <x-donate-button variant="footer">Donate</x-donate-button>
        </div>
    </div>
</footer>
