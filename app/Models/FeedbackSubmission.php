<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class FeedbackSubmission
 *
 * Represents user-submitted feedback, feature requests, bug reports, or dark-sky spot suggestions.
 *
 * @property int $id
 * @property int|null $user_id Authenticated user ID (optional)
 * @property string $type Submission type ('spot_suggestion', 'feature_request', 'bug_report', 'general')
 * @property string $status Workflow status ('pending', 'approved', 'rejected', 'resolved')
 * @property string $name Submitter name
 * @property string $email Submitter email address
 * @property string $title Title or suggested spot name
 * @property string $description Detailed message or submission notes
 * @property array<string, mixed>|null $meta Metadata payload (geospatial coordinates, browser info, etc.)
 * @property string|null $admin_notes Internal administrative review notes
 * @property Carbon|null $resolved_at Timestamp when marked approved, rejected, or resolved
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 *
 * @method static Builder|FeedbackSubmission pending() Scope query to pending submissions
 * @method static Builder|FeedbackSubmission ofType(string $type) Scope query by submission type
 *
 * @mixin Builder
 */
class FeedbackSubmission extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'feedback_submissions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'status',
        'name',
        'email',
        'title',
        'description',
        'meta',
        'admin_notes',
        'resolved_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'resolved_at' => 'datetime',
        ];
    }

    /**
     * User who submitted the feedback (if authenticated).
     *
     * @return BelongsTo<User, FeedbackSubmission>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include pending submissions.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to a specific submission type.
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Get human-readable type label.
     */
    public function getTypeLabel(): string
    {
        return match ($this->type) {
            'spot_suggestion' => 'Spot Suggestion',
            'feature_request' => 'Feature Request',
            'bug_report' => 'Bug Report',
            default => 'General Feedback',
        };
    }

    /**
     * Tailwind CSS badge styling classes for submission type.
     */
    public function getTypeBadgeClass(): string
    {
        return match ($this->type) {
            'spot_suggestion' => 'bg-emerald-950/70 border border-emerald-500/40 text-emerald-300',
            'feature_request' => 'bg-purple-950/70 border border-purple-500/40 text-purple-300',
            'bug_report' => 'bg-rose-950/70 border border-rose-500/40 text-rose-300',
            default => 'bg-blue-950/70 border border-blue-500/40 text-blue-300',
        };
    }

    /**
     * Tailwind CSS badge styling classes for workflow status.
     */
    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            'approved' => 'bg-emerald-900/60 border border-emerald-500/40 text-emerald-200',
            'resolved' => 'bg-teal-900/60 border border-teal-500/40 text-teal-200',
            'rejected' => 'bg-rose-900/60 border border-rose-500/40 text-rose-200',
            default => 'bg-amber-900/60 border border-amber-500/40 text-amber-200 animate-pulse',
        };
    }
}
