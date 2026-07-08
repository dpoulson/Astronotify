<x-mail::message>
# Optimal Stargazing Forecast 🌌

Hello {{ $userName }},

Good news! We've found some excellent stargazing windows in the upcoming forecast. Here is a summary of the optimal nights detected for your locations:

@foreach(collect($alerts)->groupBy('location_name') as $locationName => $nights)
### 📍 {{ $locationName }}
@foreach($nights as $night)
- **{{ \Carbon\Carbon::parse($night['date'])->format('l, F j') }}**: {{ floor($night['night_length']) }}h duration, **{{ $night['max_clear'] }}h** clear sky window.
@endforeach

@endforeach

Make sure your telescope is ready and the optics are cooled down!

<x-mail::button :url="url('/dashboard')">
View Your Dashboard
</x-mail::button>

Clear skies,<br>
{{ config('app.name') }}

---
<p style="text-align: center; font-size: 10px; color: #6b7280; margin-top: 20px;">
    You are receiving this because you enabled stargazing alerts for one of your locations. 
    <a href="{{ $unsubscribeUrl }}" style="color: #8b5cf6; text-decoration: underline;">Unsubscribe or Manage notification preferences</a>.
</p>
</x-mail::message>
