@php
    $pageTitle = 'Help & Frequently Asked Questions';
@endphp

@if(Auth::check())
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-gray-200 leading-tight">
            {{ __('Help & FAQ') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-slate-950 flex-grow text-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            @include('partials.faq-content')
        </div>
    </div>
</x-app-layout>
@else
<x-guest-layout>
    <div class="bg-slate-950 min-h-screen text-white flex flex-col">
        <!-- Guest Header -->
        <nav class="border-b border-slate-800 bg-slate-950/80 backdrop-blur-md sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                    <x-application-mark class="h-8 w-auto transform group-hover:scale-105 transition-transform" />
                    <span class="font-bold text-lg tracking-tight text-white">Astronotify</span>
                </a>
                <div class="flex items-center gap-6">
                    <a href="{{ route('about') }}" class="text-sm font-medium text-slate-300 hover:text-white transition-colors">About</a>
                    <a href="{{ route('faq') }}" class="text-sm font-medium text-purple-400">Help &amp; FAQ</a>
                    <a href="{{ route('privacy') }}" class="text-sm font-medium text-slate-300 hover:text-white transition-colors">Privacy</a>
                    <a href="{{ route('login') }}" class="text-sm font-medium text-slate-300 hover:text-white transition-colors">Log in</a>
                    <a href="{{ route('register') }}" class="text-xs font-semibold px-3.5 py-2 rounded-xl bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-500 hover:to-purple-500 text-white shadow-lg transition-transform transform hover:scale-105">Get Started</a>
                </div>
            </div>
        </nav>

        <div class="py-12 flex-grow max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8 w-full">
            @include('partials.faq-content')
        </div>
    </div>
</x-guest-layout>
@endif
