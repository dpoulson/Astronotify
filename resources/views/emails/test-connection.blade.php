<x-mail::message>
# Astronotify Mail Test 🚀

Hello!

This email confirms that your mail server configuration is correct and Astronotify can successfully send outbound emails.

**Sent At:** {{ $sentAt }}
**Mail Driver:** {{ config('mail.default') }}
**Queue Driver:** {{ config('queue.default') }}

If you received this, the basic sending mechanism is working!

Clear skies,<br>
{{ config('app.name') }}
</x-mail::message>
