<x-mail::message>
# Optimal Stargazing Conditions Approaching! 🌌

Hello {{ $location->user->name }},

Good news! We've detected excellent stargazing conditions for your location: **{{ $location->name }}**.

### Upcoming Night Forecast ({{ \Carbon\Carbon::parse($optimalDate)->format('l, F j') }}):
- **Night Duration:** {{ floor($nightLength) }} hours
- **Maximum Clear Window:** {{ $maxClear }} consecutive hours

Make sure your telescope is ready and the optics are cooled down!

<x-mail::button :url="url('/dashboard')">
View Location Dashboard
</x-mail::button>

Clear skies,<br>
{{ config('app.name') }}
</x-mail::message>
