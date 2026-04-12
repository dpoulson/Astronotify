<footer class="bg-slate-950 border-t border-slate-800 py-6 relative z-10 w-full mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center text-slate-400 text-sm">
        <div class="mb-4 md:mb-0 flex flex-col md:flex-row items-center md:items-start text-center md:text-left space-y-1 md:space-y-0 md:space-x-1">
            <span>&copy; {{ date('Y') }} {{ config('app.name', 'Astronotify') }}.</span>
            <span>A project by <a href="https://we-make-things.co.uk/" target="_blank" rel="noopener noreferrer" class="text-purple-400 hover:text-purple-300 font-semibold transition-colors">We Make Things</a>.</span>
        </div>
        
        <div class="flex items-center space-x-4">
            <span class="hidden sm:inline text-slate-600">|</span>
            <span class="text-xs text-slate-500">Love the service?</span>
            <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank" class="inline-flex m-0">
                <input type="hidden" name="cmd" value="_donations" />
                <input type="hidden" name="business" value="admin@we-make-things.co.uk" />
                <input type="hidden" name="currency_code" value="GBP" />
                <button type="submit" class="group flex items-center space-x-1.5 text-slate-300 hover:text-blue-400 transition-colors bg-slate-900 hover:bg-slate-800 border border-slate-700 px-4 py-1.5 rounded-full text-xs font-bold shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                    <span>Donate via PayPal</span>
                </button>
            </form>
        </div>
    </div>
</footer>
