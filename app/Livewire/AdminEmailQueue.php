<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AdminEmailQueue extends Component
{
    use WithPagination;

    public function clearQueue()
    {
        DB::table('jobs')->delete();
        session()->flash('message', 'Queue cleared successfully.');
    }

    public function deleteJob($id)
    {
        DB::table('jobs')->where('id', $id)->delete();
        session()->flash('message', 'Job deleted successfully.');
    }

    public function render()
    {
        $jobs = DB::table('jobs')->paginate(20);

        $jobs->getCollection()->transform(function ($job) {
            $payload = json_decode($job->payload, true);
            $job->display_name = $payload['displayName'] ?? 'Unknown';
            
            // Try to extract more useful info from command
            if (isset($payload['data']['command'])) {
                try {
                    $command = unserialize($payload['data']['command']);
                    
                    if (isset($command->mailable)) {
                        $mailable = $command->mailable;
                        $job->mailable_class = get_class($mailable);
                        
                        // Extract recipient if possible
                        if (isset($mailable->to) && is_array($mailable->to) && count($mailable->to) > 0) {
                            $job->recipient = $mailable->to[0]['address'] ?? 'Unknown';
                        }
                    } elseif ($command instanceof \Illuminate\Events\CallQueuedListener && isset($command->data[0])) {
                        // Handle events that send mail
                        $event = $command->data[0];
                        $job->display_name = get_class($event);
                    }
                } catch (\Exception $e) {
                    // Fail gracefully if unserialize fails
                }
            }

            return $job;
        });

        return view('livewire.admin-email-queue', [
            'jobs' => $jobs,
        ])->layout('layouts.app');
    }
}
