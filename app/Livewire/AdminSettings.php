<?php

namespace App\Livewire;

use App\Models\Setting;
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
     * Initialize component properties from database settings.
     */
    public function mount(): void
    {
        $this->forecast_days = (int) Setting::get('forecast_days', 7);
        $this->grouping_decimal_places = (int) Setting::get('grouping_decimal_places', 1);
        $this->conjunction_threshold = (float) Setting::get('conjunction_threshold', 0.75);
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
        ]);

        Setting::set('forecast_days', $this->forecast_days);
        Setting::set('grouping_decimal_places', $this->grouping_decimal_places);
        Setting::set('conjunction_threshold', $this->conjunction_threshold);

        session()->flash('message', 'Global settings successfully updated.');
    }

    /**
     * Render the admin settings blade template.
     */
    public function render(): View
    {
        return view('livewire.admin-settings')->layout('layouts.app');
    }
}
