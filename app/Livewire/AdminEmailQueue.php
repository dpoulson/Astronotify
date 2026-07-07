<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Artisan;
use App\Mail\TestConnectionMail;

class AdminEmailQueue extends Component
{
    use WithPagination;

    // Test Email fields
    public $testEmailAddress = '';
    public $testEmailStatus = null;
    public $testEmailErrorDetails = null;

    // Modal exception details
    public $selectedException = null;

    protected $rules = [
        'testEmailAddress' => 'required|email',
    ];

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

    public function sendTestEmail()
    {
        $this->validate();

        $this->testEmailStatus = 'sending';
        $this->testEmailErrorDetails = null;

        try {
            // Send synchronously to bypass any queue driver issues and verify credentials directly
            Mail::to($this->testEmailAddress)->send(new TestConnectionMail(now()->toDateTimeString()));
            $this->testEmailStatus = 'success';
            $this->testEmailAddress = '';
            session()->flash('message', 'Direct test email sent successfully! Check your inbox.');
        } catch (\Exception $e) {
            $this->testEmailStatus = 'error';
            $this->testEmailErrorDetails = $e->getMessage() . "\n\n" . $e->getTraceAsString();
        }
    }

    public function showException($id)
    {
        $job = DB::table('failed_jobs')->where('id', $id)->first();
        if ($job) {
            $this->selectedException = [
                'id' => $job->id,
                'uuid' => $job->uuid,
                'exception' => $job->exception,
            ];
        }
    }

    public function closeExceptionModal()
    {
        $this->selectedException = null;
    }

    public function retryJob($uuid)
    {
        try {
            Artisan::call('queue:retry', ['id' => $uuid]);
            session()->flash('message', "Job [{$uuid}] has been pushed back to the queue.");
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to retry job: ' . $e->getMessage());
        }
    }

    public function deleteFailedJob($id)
    {
        DB::table('failed_jobs')->where('id', $id)->delete();
        session()->flash('message', 'Failed job record deleted.');
    }

    public function clearFailedJobs()
    {
        DB::table('failed_jobs')->delete();
        session()->flash('message', 'All failed jobs cleared successfully.');
    }

    public function render()
    {
        $jobs = DB::table('jobs')->paginate(10, ['*'], 'pendingPage');
        $failedJobs = DB::table('failed_jobs')->paginate(10, ['*'], 'failedPage');

        // Transform pending jobs for friendly presentation
        $jobs->getCollection()->transform(function ($job) {
            $payload = json_decode($job->payload, true);
            $job->display_name = $payload['displayName'] ?? 'Unknown';
            
            if (isset($payload['data']['command'])) {
                try {
                    $command = unserialize($payload['data']['command']);
                    if (isset($command->mailable)) {
                        $mailable = $command->mailable;
                        $job->mailable_class = get_class($mailable);
                        
                        if (isset($mailable->to) && is_array($mailable->to) && count($mailable->to) > 0) {
                            $job->recipient = $mailable->to[0]['address'] ?? 'Unknown';
                        }
                    } elseif ($command instanceof \Illuminate\Events\CallQueuedListener && isset($command->data[0])) {
                        $event = $command->data[0];
                        $job->display_name = get_class($event);
                    }
                } catch (\Exception $e) {
                    // Graceful fail
                }
            }
            return $job;
        });

        // Transform failed jobs for friendly presentation
        $failedJobs->getCollection()->transform(function ($job) {
            $payload = json_decode($job->payload, true);
            $job->display_name = $payload['displayName'] ?? 'Unknown';
            
            if (isset($payload['data']['command'])) {
                try {
                    $command = unserialize($payload['data']['command']);
                    if (isset($command->mailable)) {
                        $mailable = $command->mailable;
                        $job->mailable_class = get_class($mailable);
                        
                        if (isset($mailable->to) && is_array($mailable->to) && count($mailable->to) > 0) {
                            $job->recipient = $mailable->to[0]['address'] ?? 'Unknown';
                        }
                    }
                } catch (\Exception $e) {
                    // Graceful fail
                }
            }
            return $job;
        });

        return view('livewire.admin-email-queue', [
            'jobs' => $jobs,
            'failedJobs' => $failedJobs,
        ])->layout('layouts.app');
    }
}
