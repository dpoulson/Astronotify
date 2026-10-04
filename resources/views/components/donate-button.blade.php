@props([
    'variant' => 'primary',
    'class' => '',
])

@php
    $buttonStyles = match($variant) {
        'footer' => 'group flex items-center space-x-1.5 text-slate-300 hover:text-rose-400 transition-colors bg-slate-900 hover:bg-slate-800 border border-slate-700 px-4 py-1.5 rounded-full text-xs font-bold shadow-sm focus:outline-none focus:ring-2 focus:ring-rose-500/50',
        'subtle' => 'group inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-900/90 hover:bg-slate-800 border border-slate-700/80 hover:border-slate-600 text-slate-200 hover:text-white font-bold text-xs shadow-md transition-all focus:outline-none focus:ring-2 focus:ring-rose-500/50',
        default => 'group inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-rose-600 via-purple-600 to-indigo-600 hover:from-rose-500 hover:via-purple-500 hover:to-indigo-500 text-white font-bold text-xs sm:text-sm shadow-lg shadow-purple-900/30 hover:shadow-purple-700/50 transform hover:scale-[1.02] active:scale-[0.98] transition-all focus:outline-none focus:ring-2 focus:ring-rose-400',
    };

    $iconStyles = match($variant) {
        'footer' => 'w-3 h-3',
        default => 'w-3.5 h-3.5 text-rose-200 fill-current group-hover:scale-110 transition-transform',
    };
@endphp

<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank" class="inline-flex m-0 {{ $class }}">
    <input type="hidden" name="cmd" value="_donations" />
    <input type="hidden" name="business" value="admin@we-make-things.co.uk" />
    <input type="hidden" name="currency_code" value="GBP" />
    <button type="submit" {{ $attributes->merge(['class' => $buttonStyles]) }}>
        <svg class="{{ $iconStyles }}" fill="{{ $variant === 'footer' ? 'none' : 'currentColor' }}" stroke="{{ $variant === 'footer' ? 'currentColor' : 'none' }}" viewBox="0 0 24 24">
            @if($variant === 'footer')
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            @else
                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
            @endif
        </svg>
        <span>{{ $slot->isEmpty() ? 'Donate' : $slot }}</span>
    </button>
</form>
