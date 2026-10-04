<?php

namespace App\Livewire;

use App\Models\FeedbackSubmission;
use App\Models\Setting;
use App\Services\DiscordWebhookService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Class FeedbackModal
 *
 * Global interactive modal for submitting dark-sky spot suggestions,
 * feature requests, bug diagnostics, and general feedback with Discord alerts.
 */
class FeedbackModal extends Component
{
    public bool $isOpen = false;

    public bool $submitted = false;

    // Submitter Info
    public string $name = '';

    public string $email = '';

    public string $type = 'spot_suggestion';

    public string $title = '';

    public string $description = '';

    // Spot-specific metadata
    public string $country = '';

    public string $region = '';

    public string $latitude = '';

    public string $longitude = '';

    public string $elevation = '';

    public int $bortle_class = 2;

    public string $dark_sky_status = '';

    // Anti-spam honeypot
    public string $website = '';

    // Diagnostics
    public string $currentUrl = '';

    public string $userAgent = '';

    public string $discordInviteUrl = 'https://discord.gg/UuwaXjRjZU';

    /**
     * Component mount lifecycle.
     */
    public function mount(): void
    {
        $this->discordInviteUrl = (string) Setting::get('discord_invite_url', 'https://discord.gg/UuwaXjRjZU');

        if (Auth::check()) {
            $user = Auth::user();
            $this->name = $user->name ?? '';
            $this->email = $user->email ?? '';
        }
    }

    /**
     * Open the modal with optional pre-configured type, title, and spot location.
     *
     * @param  array<string, mixed>  $params  Parameters to initialize the modal.
     */
    #[On('open-feedback-modal')]
    public function openModal(mixed $params = []): void
    {
        $this->resetValidation();
        $this->submitted = false;

        $params = is_array($params) ? $params : (array) $params;

        if (isset($params['type']) && in_array($params['type'], ['spot_suggestion', 'feature_request', 'bug_report', 'general'])) {
            $this->type = $params['type'];
        }

        if (! empty($params['title'])) {
            $this->title = $params['title'];
        }

        if (! empty($params['country'])) {
            $this->country = $params['country'];
        }

        if (! empty($params['region'])) {
            $this->region = $params['region'];
        }

        if (isset($params['latitude'])) {
            $this->latitude = (string) $params['latitude'];
        }

        if (isset($params['longitude'])) {
            $this->longitude = (string) $params['longitude'];
        }

        if (isset($params['url'])) {
            $this->currentUrl = (string) $params['url'];
        }

        $this->discordInviteUrl = (string) Setting::get('discord_invite_url', 'https://discord.gg/UuwaXjRjZU');

        if (Auth::check() && empty($this->name)) {
            $user = Auth::user();
            $this->name = $user->name ?? '';
            $this->email = $user->email ?? '';
        }

        $this->isOpen = true;
    }

    /**
     * Close the feedback modal and reset submission state.
     */
    public function closeModal(): void
    {
        $this->isOpen = false;
        $this->submitted = false;
    }

    /**
     * Validate and process the feedback submission.
     */
    public function submit(DiscordWebhookService $discordService): void
    {
        // Silent bot rejection via honeypot
        if (! empty($this->website)) {
            $this->submitted = true;

            return;
        }

        $rules = [
            'name' => 'required|string|min:2|max:100',
            'email' => 'required|email|max:150',
            'type' => 'required|in:spot_suggestion,feature_request,bug_report,general',
            'title' => 'required|string|min:3|max:150',
            'description' => 'required|string|min:10|max:3000',
        ];

        if ($this->type === 'spot_suggestion') {
            $rules['country'] = 'required|string|min:2|max:80';
            $rules['latitude'] = 'nullable|numeric|between:-90,90';
            $rules['longitude'] = 'nullable|numeric|between:-180,180';
            $rules['elevation'] = 'nullable|integer|min:-500|max:9000';
            $rules['bortle_class'] = 'required|integer|min:1|max:9';
            $rules['dark_sky_status'] = 'nullable|string|max:100';
        }

        $this->validate($rules);

        $meta = [];

        if ($this->type === 'spot_suggestion') {
            $meta = [
                'country' => $this->country,
                'region' => $this->region,
                'latitude' => $this->latitude !== '' ? (float) $this->latitude : null,
                'longitude' => $this->longitude !== '' ? (float) $this->longitude : null,
                'elevation' => $this->elevation !== '' ? (int) $this->elevation : null,
                'bortle_class' => $this->bortle_class,
                'dark_sky_status' => $this->dark_sky_status ?: null,
            ];
        } elseif ($this->type === 'bug_report') {
            $meta = [
                'url' => $this->currentUrl,
                'user_agent' => $this->userAgent,
            ];
        }

        $submission = FeedbackSubmission::create([
            'user_id' => Auth::id(),
            'type' => $this->type,
            'status' => 'pending',
            'name' => $this->name,
            'email' => $this->email,
            'title' => $this->title,
            'description' => $this->description,
            'meta' => ! empty($meta) ? $meta : null,
        ]);

        // Dispatch async Discord webhook notification
        $discordService->sendFeedbackNotification($submission);

        $this->submitted = true;
    }

    /**
     * Render the feedback modal view.
     */
    public function render(): View
    {
        return view('livewire.feedback-modal');
    }
}
