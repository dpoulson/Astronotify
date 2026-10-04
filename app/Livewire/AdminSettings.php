<?php

namespace App\Livewire;

use App\Livewire\Traits\AuthorizesAdminAccess;
use App\Models\Setting;
use App\Services\DiscordWebhookService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Class AdminSettings
 *
 * Livewire component managing system-wide configuration thresholds,
 * forecast horizons, and conjunction calculation parameters.
 */
class AdminSettings extends Component
{
    use AuthorizesAdminAccess;

    /**
     * Number of forecast days to request from Open-Meteo.
     *
     * @var int|string
     */
    public $forecast_days = 7;

    /**
     * Number of decimal places used to bin geographic coordinates.
     *
     * @var int|string
     */
    public $grouping_decimal_places = 1;

    /**
     * Maximum angular separation in degrees for conjunction threshold.
     *
     * @var float|string
     */
    public $conjunction_threshold = 0.75;

    /**
     * Maximum observing locations allowed per regular user.
     *
     * @var int|string
     */
    public $max_locations_per_user = 10;

    /**
     * Discord Webhook URL for real-time community feedback and spot notifications.
     */
    public string $discord_feedback_webhook_url = '';

    /**
     * Public Discord server invite URL.
     */
    public string $discord_invite_url = 'https://discord.gg/UuwaXjRjZU';

    /**
     * Initialize component properties from database settings.
     */
    public function mount(): void
    {
        $this->forecast_days = (int) Setting::get('forecast_days', 7);
        $this->grouping_decimal_places = (int) Setting::get('grouping_decimal_places', 1);
        $this->conjunction_threshold = (float) Setting::get('conjunction_threshold', 0.75);
        $this->max_locations_per_user = (int) Setting::get('max_locations_per_user', 10);
        $this->discord_feedback_webhook_url = (string) Setting::get('discord_feedback_webhook_url', '');
        $this->discord_invite_url = (string) Setting::get('discord_invite_url', 'https://discord.gg/UuwaXjRjZU');
    }

    /**
     * Validate and save the global application settings.
     */
    public function save(): void
    {
        $this->validate([
            'forecast_days' => 'required|integer|min:1|max:16',
            'grouping_decimal_places' => 'required|integer|min:0|max:4',
            'conjunction_threshold' => 'required|numeric|min:0.01|max:5.0',
            'max_locations_per_user' => 'required|integer|min:1|max:100',
            'discord_feedback_webhook_url' => 'nullable|url|max:255',
            'discord_invite_url' => 'required|url|max:255',
        ]);

        Setting::set('forecast_days', $this->forecast_days);
        Setting::set('grouping_decimal_places', $this->grouping_decimal_places);
        Setting::set('conjunction_threshold', $this->conjunction_threshold);
        Setting::set('max_locations_per_user', $this->max_locations_per_user);
        Setting::set('discord_feedback_webhook_url', $this->discord_feedback_webhook_url);
        Setting::set('discord_invite_url', $this->discord_invite_url);

        session()->flash('message', 'Global settings successfully updated.');
    }

    /**
     * Send a test ping to the configured Discord webhook.
     */
    public function testDiscordWebhook(DiscordWebhookService $service): void
    {
        $this->validate([
            'discord_feedback_webhook_url' => 'required|url',
        ]);

        $success = $service->sendTestNotification($this->discord_feedback_webhook_url);

        if ($success) {
            session()->flash('webhook_message', 'Discord test notification successfully delivered!');
        } else {
            session()->flash('webhook_error', 'Failed to reach Discord. Please check the webhook URL.');
        }
    }

    /**
     * Render the admin settings blade template.
     */
    public function render(): View
    {
        return view('livewire.admin-settings')->layout('layouts.app');
    }
}
