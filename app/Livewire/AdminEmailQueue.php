<?php

namespace App\Livewire;

use App\Livewire\Traits\AuthorizesAdminAccess;
use App\Mail\TestConnectionMail;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Events\CallQueuedListener;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Class AdminEmailQueue
 *
 * Administrative Livewire component for inspecting queued mail jobs, examining failed job
 * stack traces, retrying/purging failed tasks, and performing live synchronous SMTP connectivity tests.
 */
class AdminEmailQueue extends Component
{
    use AuthorizesAdminAccess;
    use WithPagination;

    // Test Email fields
    public string $testEmailAddress = '';

    public ?string $testEmailStatus = null;

    public ?string $testEmailErrorDetails = null;

    // Modal exception details
    public ?array $selectedException = null;

    /**
     * Validation rules for interactive operations.
     *
     * @var array<string, string>
     */
    protected $rules = [
        'testEmailAddress' => 'required|email',
    ];

    /**
     * Purge all pending jobs from the queue table.
     */
    public function clearQueue(): void
    {
        DB::table('jobs')->delete();
        session()->flash('message', 'Queue cleared successfully.');
    }

    /**
     * Delete an individual pending job by ID.
     */
    public function deleteJob(int|string $id): void
    {
        DB::table('jobs')->where('id', $id)->delete();
        session()->flash('message', 'Job deleted successfully.');
    }

    /**
     * Send a live synchronous test email to verify SMTP configuration directly.
     */
    public function sendTestEmail(): void
    {
        $this->validate();

        $this->testEmailStatus = 'sending';
        $this->testEmailErrorDetails = null;

        try {
            Mail::to($this->testEmailAddress)->send(new TestConnectionMail(now()->toDateTimeString()));
            $this->testEmailStatus = 'success';
            $this->testEmailAddress = '';
            session()->flash('message', 'Direct test email sent successfully! Check your inbox.');
        } catch (Exception $e) {
            $this->testEmailStatus = 'error';
            $this->testEmailErrorDetails = $e->getMessage()."\n\n".$e->getTraceAsString();
        }
    }

    /**
     * Load exception details for display in the inspector modal.
     *
     * @param  int|string  $id  Failed job ID.
     */
    public function showException(int|string $id): void
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

    /**
     * Close the exception inspection modal.
     */
    public function closeExceptionModal(): void
    {
        $this->selectedException = null;
    }

    /**
     * Retry an individual failed job by its UUID.
     *
     * @param  string  $uuid  Job UUID.
     */
    public function retryJob(string $uuid): void
    {
        try {
            Artisan::call('queue:retry', ['id' => $uuid]);
            session()->flash('message', "Job [{$uuid}] has been pushed back to the queue.");
        } catch (Exception $e) {
            session()->flash('error', 'Failed to retry job: '.$e->getMessage());
        }
    }

    /**
     * Delete an individual failed job record by ID.
     *
     * @param  int|string  $id  Failed job ID.
     */
    public function deleteFailedJob(int|string $id): void
    {
        DB::table('failed_jobs')->where('id', $id)->delete();
        session()->flash('message', 'Failed job record deleted.');
    }

    /**
     * Delete all failed job records.
     */
    public function clearFailedJobs(): void
    {
        DB::table('failed_jobs')->delete();
        session()->flash('message', 'All failed jobs cleared successfully.');
    }

    /**
     * Transform a database job row to extract friendly display titles and mailable recipients.
     *
     * @param  object  $job  Job record object.
     * @return object Transformed job object.
     */
    private function transformJobPayload(object $job): object
    {
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
                } elseif ($command instanceof CallQueuedListener && isset($command->data[0])) {
                    $event = $command->data[0];
                    $job->display_name = get_class($event);
                }
            } catch (Exception $e) {
                // Graceful fallback for corrupted payloads
            }
        }

        return $job;
    }

    /**
     * Render the admin email queue monitor.
     */
    public function render(): View
    {
        $jobs = DB::table('jobs')->paginate(10, ['*'], 'pendingPage');
        $failedJobs = DB::table('failed_jobs')->paginate(10, ['*'], 'failedPage');

        $jobs->getCollection()->transform(fn (object $job) => $this->transformJobPayload($job));
        $failedJobs->getCollection()->transform(fn (object $job) => $this->transformJobPayload($job));

        return view('livewire.admin-email-queue', [
            'jobs' => $jobs,
            'failedJobs' => $failedJobs,
        ])->layout('layouts.app');
    }
}
