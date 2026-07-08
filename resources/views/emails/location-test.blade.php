<x-mail::message>
# Stargazing Test Alert 📡

Hello {{ $userName }},

This is a test notification sent from your location card for **{{ $location->name }}**.

If you're reading this, your email configuration is working perfectly! Here are the alert parameters configured for this location:

- **Coordinates:** Lat {{ $location->latitude }}, Lon {{ $location->longitude }}
- **Min Night Length:** {{ $location->min_night_length_hours }} hours
- **Min Clear Sky Window:** {{ $location->min_clear_hours }} hours
- **Max Wind Speed:** {{ $location->max_wind_speed }} km/h
- **Max Cloud Cover:** {{ $location->max_cloud_cover }}%
- **Stargazing Weather Alerts:** {{ $location->notify_stargazing_alerts ? 'Enabled 🔔' : 'Disabled 🔕' }}
- **ISS Solar Transits:** {{ $location->notify_iss_sun_transit ? 'Enabled ☀️' : 'Disabled' }}
- **ISS Lunar Transits:** {{ $location->notify_iss_moon_transit ? 'Enabled 🌙' : 'Disabled' }}

<x-mail::button :url="url('/dashboard')">
Go to Dashboard
</x-mail::button>

Clear skies,<br>
{{ config('app.name') }}
</x-mail::message>
