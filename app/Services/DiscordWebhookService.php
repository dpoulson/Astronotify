<?php

namespace App\Services;

use App\Models\FeedbackSubmission;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Class DiscordWebhookService
 *
 * Dispatches rich structured notifications to configured Discord channels
 * via Discord Incoming Webhooks for feedback, spot submissions, and bug reports.
 */
class DiscordWebhookService
{
    /**
     * Send a rich embed notification to Discord for a new feedback submission.
     *
     * @param  FeedbackSubmission  $submission  The persisted submission instance.
     * @return bool True if dispatched successfully, false otherwise.
     */
    public function sendFeedbackNotification(FeedbackSubmission $submission): bool
    {
        $webhookUrl = Setting::get('discord_feedback_webhook_url')
            ?: config('services.discord.webhook_url')
            ?: env('DISCORD_FEEDBACK_WEBHOOK_URL');

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

        try {
            $response = Http::timeout(5)->post($webhookUrl, $payload);

            return $response->successful();
        } catch (Throwable $e) {
            Log::warning('Failed to dispatch Discord webhook for feedback submission', [
                'submission_id' => $submission->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
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
                    'description' => 'Astronotify is connected to your Discord server! Dark sky spot suggestions, feature requests, and community feedback will broadcast to this channel in real time.',
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

        try {
            $response = Http::timeout(5)->post($webhookUrl, $payload);

            return $response->successful();
        } catch (Throwable $e) {
            Log::warning('Discord webhook test failed', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
