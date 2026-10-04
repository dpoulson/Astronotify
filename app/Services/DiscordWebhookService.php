<?php

namespace App\Services;

use App\Models\FeedbackSubmission;
use App\Models\Location;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Class DiscordWebhookService
 *
 * Dispatches rich structured notifications to configured Discord channels
 * via Discord Incoming Webhooks for user registrations, new observing locations,
 * feedback, spot submissions, and bug reports.
 */
class DiscordWebhookService
{
    /**
     * Retrieve the configured Discord webhook URL.
     */
    public function getWebhookUrl(): ?string
    {
        return Setting::get('discord_feedback_webhook_url')
            ?: Setting::get('discord_webhook_url')
            ?: config('services.discord.webhook_url')
            ?: env('DISCORD_FEEDBACK_WEBHOOK_URL')
            ?: env('DISCORD_WEBHOOK_URL');
    }

    /**
     * Dispatch an arbitrary payload to a Discord webhook endpoint.
     *
     * @param  array<string, mixed>  $payload  Discord webhook payload with embeds.
     * @param  string|null  $webhookUrl  Target webhook URL (defaults to configured URL).
     * @param  string  $context  Logging context.
     * @return bool True if dispatched successfully, false otherwise.
     */
    public function sendPayload(array $payload, ?string $webhookUrl = null, string $context = 'Discord notification'): bool
    {
        $url = $webhookUrl ?: $this->getWebhookUrl();

        if (empty($url)) {
            return false;
        }

        try {
            $response = Http::timeout(5)->post($url, $payload);

            return $response->successful();
        } catch (Throwable $e) {
            Log::warning("Failed to dispatch {$context}", [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send a rich embed notification to Discord for a new feedback submission.
     *
     * @param  FeedbackSubmission  $submission  The persisted submission instance.
     * @return bool True if dispatched successfully, false otherwise.
     */
    public function sendFeedbackNotification(FeedbackSubmission $submission): bool
    {
        $webhookUrl = $this->getWebhookUrl();

        if (empty($webhookUrl)) {
            return false;
        }

        $color = match ($submission->type) {
            'spot_suggestion' => 0x10B981, // Emerald
            'feature_request' => 0x8B5CF6, // Purple
            'bug_report' => 0xEF4444,      // Red
            default => 0x3B82F6,           // Blue
        };

        $emoji = match ($submission->type) {
            'spot_suggestion' => '🔭',
            'feature_request' => '💡',
            'bug_report' => '🐛',
            default => '📬',
        };

        $fields = [
            [
                'name' => 'Submitter',
                'value' => "{$submission->name} (`{$submission->email}`)".($submission->user_id ? " [User #{$submission->user_id}]" : ' [Guest]'),
                'inline' => true,
            ],
            [
                'name' => 'Category',
                'value' => "{$emoji} {$submission->getTypeLabel()}",
                'inline' => true,
            ],
            [
                'name' => 'Status',
                'value' => ucfirst($submission->status),
                'inline' => true,
            ],
        ];

        // Append spot suggestion metadata if available
        if ($submission->type === 'spot_suggestion' && is_array($submission->meta)) {
            $spotFields = [];
            if (! empty($submission->meta['country'])) {
                $loc = $submission->meta['country'];
                if (! empty($submission->meta['region'])) {
                    $loc = "{$submission->meta['region']}, {$loc}";
                }
                $spotFields[] = "**Location:** {$loc}";
            }

            if (isset($submission->meta['latitude'], $submission->meta['longitude'])) {
                $lat = round((float) $submission->meta['latitude'], 4);
                $lng = round((float) $submission->meta['longitude'], 4);
                $spotFields[] = "**Coordinates:** `{$lat}°, {$lng}°`";
            }

            if (! empty($submission->meta['bortle_class'])) {
                $spotFields[] = "**Bortle Rating:** Class {$submission->meta['bortle_class']}";
            }

            if (! empty($submission->meta['dark_sky_status'])) {
                $spotFields[] = "**Designation:** {$submission->meta['dark_sky_status']}";
            }

            if (! empty($spotFields)) {
                $fields[] = [
                    'name' => 'Spot Specifications',
                    'value' => implode("\n", $spotFields),
                    'inline' => false,
                ];
            }
        }

        // Append bug report metadata if available
        if ($submission->type === 'bug_report' && is_array($submission->meta)) {
            $envDetails = [];
            if (! empty($submission->meta['url'])) {
                $envDetails[] = "**Page URL:** {$submission->meta['url']}";
            }
            if (! empty($submission->meta['user_agent'])) {
                $envDetails[] = '**Browser/OS:** '.Str::limit($submission->meta['user_agent'], 80);
            }
            if (! empty($envDetails)) {
                $fields[] = [
                    'name' => 'Client Diagnostics',
                    'value' => implode("\n", $envDetails),
                    'inline' => false,
                ];
            }
        }

        $payload = [
            'username' => 'Astronotify Support',
            'avatar_url' => asset('images/icon-192.png'),
            'embeds' => [
                [
                    'title' => "{$emoji} New {$submission->getTypeLabel()}: {$submission->title}",
                    'description' => Str::limit($submission->description, 1000),
                    'color' => $color,
                    'fields' => $fields,
                    'footer' => [
                        'text' => 'Astronotify Community Dispatcher • Review in /admin/requests',
                    ],
                    'timestamp' => now()->toIso8601String(),
                ],
            ],
        ];

        return $this->sendPayload($payload, null, "Discord webhook for feedback submission #{$submission->id}");
    }

    /**
     * Send a rich embed notification to Discord when a new user registers.
     *
     * @param  User  $user  The newly registered user instance.
     * @return bool True if dispatched successfully, false otherwise.
     */
    public function sendUserRegisteredNotification(User $user): bool
    {
        $name = $user->name ?: 'New User';
        $email = $user->email ?: 'No email';
        $registrationMethod = ! empty($user->google_id) ? '🌐 Google OAuth' : '✉️ Email & Password';

        $fields = [
            [
                'name' => 'Stargazer',
                'value' => "{$name} (`{$email}`)",
                'inline' => true,
            ],
            [
                'name' => 'User ID',
                'value' => "#{$user->id}",
                'inline' => true,
            ],
            [
                'name' => 'Sign-up Method',
                'value' => $registrationMethod,
                'inline' => true,
            ],
            [
                'name' => 'Total Astronomers',
                'value' => (string) User::count(),
                'inline' => true,
            ],
        ];

        $payload = [
            'username' => 'Astronotify Bot',
            'avatar_url' => asset('images/icon-192.png'),
            'embeds' => [
                [
                    'title' => '✨ New Astronomer Registered',
                    'description' => "Welcome **{$name}** to the Astronotify community! Observation tracking and transit alerts are now active.",
                    'color' => 0x6366F1, // Indigo
                    'fields' => $fields,
                    'footer' => [
                        'text' => 'Astronotify User Dispatcher',
                    ],
                    'timestamp' => now()->toIso8601String(),
                ],
            ],
        ];

        return $this->sendPayload($payload, null, "Discord webhook for new user registration #{$user->id}");
    }

    /**
     * Send a rich embed notification to Discord when a user creates a new observing location.
     *
     * @param  Location  $location  The newly created location instance.
     * @return bool True if dispatched successfully, false otherwise.
     */
    public function sendLocationCreatedNotification(Location $location): bool
    {
        $user = $location->user ?? User::find($location->user_id);
        $userName = $user ? $user->name : 'Unknown User';
        $userEmail = $user ? $user->email : 'N/A';
        $userLabel = $user ? "{$userName} (`{$userEmail}`)" : "User #{$location->user_id}";

        $lat = round((float) $location->latitude, 4);
        $lng = round((float) $location->longitude, 4);
        $coordValue = "`{$lat}°, {$lng}°`";
        if ($location->elevation !== null) {
            $coordValue .= " • {$location->elevation}m elevation";
        }

        $bortleDesc = $location->bortle_description ? " ({$location->bortle_description})" : '';
        $bortleValue = $location->bortle
            ? "Class {$location->bortle}{$bortleDesc}"
            : 'Unspecified';

        $alerts = [];
        if ($location->notify_stargazing_alerts) {
            $alerts[] = '✨ Stargazing Forecasts';
        }
        if ($location->notify_iss_sun_transit) {
            $alerts[] = '☀️ ISS Solar Transits';
        }
        if ($location->notify_iss_moon_transit) {
            $alerts[] = '🌙 ISS Lunar Transits';
        }
        $alertSummary = ! empty($alerts) ? implode(' • ', $alerts) : 'None';

        $fields = [
            [
                'name' => 'Location Name',
                'value' => "📍 **{$location->name}**",
                'inline' => true,
            ],
            [
                'name' => 'Configured By',
                'value' => $userLabel,
                'inline' => true,
            ],
            [
                'name' => 'Sky Quality',
                'value' => "🔭 {$bortleValue}",
                'inline' => true,
            ],
            [
                'name' => 'Coordinates',
                'value' => $coordValue,
                'inline' => true,
            ],
            [
                'name' => 'Weather Criteria',
                'value' => "Cloud: ≤ {$location->max_cloud_cover}% • Wind: ≤ {$location->max_wind_speed} km/h • Clear: ≥ {$location->min_clear_hours}h",
                'inline' => false,
            ],
            [
                'name' => 'Alert Preferences',
                'value' => $alertSummary,
                'inline' => false,
            ],
        ];

        $payload = [
            'username' => 'Astronotify Bot',
            'avatar_url' => asset('images/icon-192.png'),
            'embeds' => [
                [
                    'title' => "🔭 New Observing Location Added: {$location->name}",
                    'description' => 'A new observing spot has been configured for stargazing forecasts and modeled ISS transit conjunctions.',
                    'color' => 0x10B981, // Emerald
                    'fields' => $fields,
                    'footer' => [
                        'text' => 'Astronotify Location Dispatcher',
                    ],
                    'timestamp' => now()->toIso8601String(),
                ],
            ],
        ];

        return $this->sendPayload($payload, null, "Discord webhook for new location #{$location->id} ({$location->name})");
    }

    /**
     * Send a verification test message to a specified Discord webhook endpoint.
     *
     * @param  string  $webhookUrl  Target webhook URL.
     * @return bool True if Discord acknowledged the payload with 204/200, false otherwise.
     */
    public function sendTestNotification(string $webhookUrl): bool
    {
        if (empty($webhookUrl)) {
            return false;
        }

        $payload = [
            'username' => 'Astronotify Bot',
            'avatar_url' => asset('images/icon-192.png'),
            'embeds' => [
                [
                    'title' => '🛰️ Discord Webhook Test Successful',
                    'description' => 'Astronotify is connected to your Discord server! User registrations, new observing locations, dark sky spot suggestions, and support requests will broadcast to this channel in real time.',
                    'color' => 0x8B5CF6,
                    'fields' => [
                        [
                            'name' => 'Environment',
                            'value' => config('app.env', 'production'),
                            'inline' => true,
                        ],
                        [
                            'name' => 'Timestamp',
                            'value' => now()->toDateTimeString(),
                            'inline' => true,
                        ],
                    ],
                    'footer' => [
                        'text' => 'Astronotify Support Engine',
                    ],
                    'timestamp' => now()->toIso8601String(),
                ],
            ],
        ];

        return $this->sendPayload($payload, $webhookUrl, 'Discord webhook test ping');
    }
}
