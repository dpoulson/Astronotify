<?php

namespace App\Livewire;

use App\Livewire\Traits\AuthorizesAdminAccess;
use App\Models\CommandLog;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Class AdminCronMonitor
 *
 * Administrative Livewire component for inspecting scheduled task history,
 * execution durations, error traces, and clearing historical command logs.
 */
class AdminCronMonitor extends Component
{
    use AuthorizesAdminAccess;
    use WithPagination;

    /**
     * Purge all historical command execution logs.
     */
    public function clearLogs(): void
    {
        CommandLog::truncate();
        session()->flash('message', 'All command logs have been cleared.');
    }

    /**
     * Delete an individual command execution log by ID.
     *
     * @param  int|string  $id  CommandLog ID.
     */
    public function deleteLog(int|string $id): void
    {
        CommandLog::destroy($id);
    }

    /**
     * Render the admin cron monitor view.
     */
    public function render(): View
    {
        $logs = CommandLog::orderBy('started_at', 'desc')->paginate(20);

        return view('livewire.admin-cron-monitor', [
            'logs' => $logs,
        ])->layout('layouts.app');
    }
}
