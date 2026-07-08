<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Setting;

class AdminSettings extends Component
{
    public $forecast_days = 7;
    public $grouping_decimal_places = 1;
    public $conjunction_threshold = 0.75;

    public function mount()
    {
        $this->forecast_days = Setting::where('key', 'forecast_days')->value('value') ?? 7;
        $this->grouping_decimal_places = Setting::where('key', 'grouping_decimal_places')->value('value') ?? 1;
        $this->conjunction_threshold = (float) (Setting::where('key', 'conjunction_threshold')->value('value') ?? 0.75);
    }

    public function save()
    {
        $this->validate([
            'forecast_days' => 'required|integer|min:1|max:16',
            'grouping_decimal_places' => 'required|integer|min:0|max:4',
            'conjunction_threshold' => 'required|numeric|min:0.01|max:5.0',
        ]);

        Setting::updateOrCreate(['key' => 'forecast_days'], ['value' => $this->forecast_days]);
        Setting::updateOrCreate(['key' => 'grouping_decimal_places'], ['value' => $this->grouping_decimal_places]);
        Setting::updateOrCreate(['key' => 'conjunction_threshold'], ['value' => $this->conjunction_threshold]);

        session()->flash('message', 'Global settings successfully updated.');
    }

    public function render()
    {
        return view('livewire.admin-settings')->layout('layouts.app');
    }
}
