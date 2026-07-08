<x-mail::message>
# ISS Solar & Lunar Transit Predictions 🛰️

Hello {{ $userName }},

Good news! We have calculated the International Space Station (ISS) orbital pass coordinates and detected upcoming solar or lunar transits/conjunctions for your configured locations:

@foreach($transits as $locData)
### 📍 {{ $locData['location_name'] }}
@foreach($locData['transits'] as $transit)
- **{{ $transit['type'] === 'sun' ? '☀️ Solar Transit/Conjunction' : '🌙 Lunar Transit/Conjunction' }}**
  - **Time (UTC):** {{ \Carbon\Carbon::parse($transit['time'])->format('l, F j, Y \a\t H:i:s') }} UTC
  - **Type:** {{ $transit['is_exact_transit'] ? 'Exact Transit (crosses face)' : 'Close Conjunction' }}
  - **Separation:** {{ $transit['separation_degrees'] }}°
  - **Position:** Altitude {{ $transit['altitude_degrees'] }}°, Azimuth {{ $transit['azimuth_degrees'] }}°
@endforeach

@endforeach

> [!IMPORTANT]
> Transit paths are extremely narrow (often only 1-3 km wide) and the actual event lasts less than a second. Double check the exact centerlines using high-resolution coordinates before setting up.

<x-mail::button :url="url('/dashboard')">
View Your Dashboard
</x-mail::button>

Clear skies,<br>
{{ config('app.name') }}

---
<p style="text-align: center; font-size: 10px; color: #6b7280; margin-top: 20px;">
    You are receiving this because you enabled ISS transit alerts for one of your locations. 
    <a href="{{ $unsubscribeUrl }}" style="color: #8b5cf6; text-decoration: underline;">Unsubscribe or Manage notification preferences</a>.
</p>
</x-mail::message>
