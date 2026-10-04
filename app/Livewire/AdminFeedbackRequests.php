<?php

namespace App\Livewire;

use App\Models\FeedbackSubmission;
use App\Models\StargazingSpot;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Class AdminFeedbackRequests
 *
 * Administrative Livewire component to manage user feedback, feature suggestions,
 * bug reports, and 1-click promotion of spot suggestions into live StargazingSpots.
 */
class AdminFeedbackRequests extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = 'pending';

    public string $typeFilter = 'all';

    public ?int $viewingSubmissionId = null;

    public string $editingAdminNotes = '';

    /**
     * Query string configuration.
     *
     * @var array<string, array<string, string>>
     */
    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'pending'],
        'typeFilter' => ['except' => 'all'],
    ];

    /**
     * Reset pagination when search or filters change.
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingTypeFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Open details modal for a specific submission.
     */
    public function viewSubmission(int $id): void
    {
        $submission = FeedbackSubmission::findOrFail($id);
        $this->viewingSubmissionId = $submission->id;
        $this->editingAdminNotes = $submission->admin_notes ?? '';
    }

    /**
     * Close details modal.
     */
    public function closeDetailsModal(): void
    {
        $this->viewingSubmissionId = null;
        $this->editingAdminNotes = '';
    }

    /**
     * Save updated administrative review notes.
     */
    public function saveAdminNotes(): void
    {
        if (! $this->viewingSubmissionId) {
            return;
        }

        $submission = FeedbackSubmission::findOrFail($this->viewingSubmissionId);
        $submission->update([
            'admin_notes' => $this->editingAdminNotes,
        ]);

        session()->flash('message', 'Administrative notes updated successfully.');
    }

    /**
     * Update the workflow status of a submission.
     */
    public function updateStatus(int $id, string $status): void
    {
        if (! in_array($status, ['pending', 'approved', 'rejected', 'resolved'])) {
            return;
        }

        $submission = FeedbackSubmission::findOrFail($id);
        $submission->update([
            'status' => $status,
            'resolved_at' => in_array($status, ['approved', 'rejected', 'resolved']) ? now() : null,
        ]);

        session()->flash('message', "Submission #{$id} marked as {$status}.");
    }

    /**
     * 1-Click Promote a Spot Suggestion into a live StargazingSpot record.
     */
    public function promoteToSpot(int $id): void
    {
        $submission = FeedbackSubmission::findOrFail($id);

        if ($submission->type !== 'spot_suggestion') {
            session()->flash('error', 'Only spot suggestions can be converted into Stargazing Spots.');

            return;
        }

        $meta = is_array($submission->meta) ? $submission->meta : [];

        // Check if coordinates exist
        $lat = isset($meta['latitude']) && is_numeric($meta['latitude']) ? (float) $meta['latitude'] : 0.0;
        $lng = isset($meta['longitude']) && is_numeric($meta['longitude']) ? (float) $meta['longitude'] : 0.0;

        $spot = StargazingSpot::create([
            'name' => $submission->title,
            'slug' => Str::slug($submission->title),
            'country' => $meta['country'] ?? 'United Kingdom',
            'region' => $meta['region'] ?? null,
            'latitude' => $lat,
            'longitude' => $lng,
            'elevation' => isset($meta['elevation']) && is_numeric($meta['elevation']) ? (int) $meta['elevation'] : 0,
            'bortle_class' => isset($meta['bortle_class']) && is_numeric($meta['bortle_class']) ? (int) $meta['bortle_class'] : 2,
            'dark_sky_status' => $meta['dark_sky_status'] ?? null,
            'description' => $submission->description,
            'is_active' => true,
        ]);

        $submissionNote = trim(($submission->admin_notes ? $submission->admin_notes."\n" : '')."Promoted to StargazingSpot #{$spot->id} ({$spot->name}) on ".now()->toFormattedDateString());

        $submission->update([
            'status' => 'approved',
            'admin_notes' => $submissionNote,
            'resolved_at' => now(),
        ]);

        $this->editingAdminNotes = $submissionNote;

        session()->flash('message', "Successfully promoted '{$spot->name}' to live Stargazing Spot (ID #{$spot->id})!");
    }

    /**
     * Delete a feedback submission.
     */
    public function deleteSubmission(int $id): void
    {
        $submission = FeedbackSubmission::findOrFail($id);
        $submission->delete();

        if ($this->viewingSubmissionId === $id) {
            $this->closeDetailsModal();
        }

        session()->flash('message', "Submission #{$id} deleted.");
    }

    /**
     * Render the admin requests blade view.
     */
    public function render(): View
    {
        $query = FeedbackSubmission::query()->with('user');

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->typeFilter !== 'all') {
            $query->where('type', $this->typeFilter);
        }

        if (! empty($this->search)) {
            $term = '%'.trim($this->search).'%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('title', 'like', $term)
                    ->orWhere('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('description', 'like', $term);
            });
        }

        $submissions = $query->latest()->paginate(15);

        $counts = [
            'pending' => FeedbackSubmission::where('status', 'pending')->count(),
            'spots' => FeedbackSubmission::where('type', 'spot_suggestion')->where('status', 'pending')->count(),
            'features' => FeedbackSubmission::where('type', 'feature_request')->where('status', 'pending')->count(),
            'bugs' => FeedbackSubmission::where('type', 'bug_report')->where('status', 'pending')->count(),
        ];

        $viewingSubmission = $this->viewingSubmissionId
            ? FeedbackSubmission::with('user')->find($this->viewingSubmissionId)
            : null;

        return view('livewire.admin-feedback-requests', [
            'submissions' => $submissions,
            'counts' => $counts,
            'viewingSubmission' => $viewingSubmission,
        ])->layout('layouts.app');
    }
}
