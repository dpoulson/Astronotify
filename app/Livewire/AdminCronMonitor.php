<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\CommandLog;
use Illuminate\Support\Facades\Schedule;

class AdminCronMonitor extends Component
{
    use WithPagination;

    public function clearLogs()
    {
        CommandLog::truncate();
        session()->flash('message', 'All command logs have been cleared.');
    }

    public function deleteLog($id)
    {
        CommandLog::destroy($id);
    }

    public function render()
    {
        $logs = CommandLog::orderBy('started_at', 'desc')->paginate(20);

        return view('livewire.admin-cron-monitor', [
            'logs' => $logs,
        ])->layout('layouts.app');
    }
}
