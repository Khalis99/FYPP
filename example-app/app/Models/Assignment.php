<?php

namespace App\Models;

use App\Enums\SubmissionStatus;
use Database\Factories\AssignmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

#[Fillable(['teacher_id', 'subject_id', 'school_class_id', 'title', 'description', 'instructions', 'due_at', 'attachment_path', 'attachment_name'])]
class Assignment extends Model
{
    /** @use HasFactory<AssignmentFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::deleting(function (Assignment $assignment): void {
            $files = $assignment->submissions()->whereNotNull('file_path')->pluck('file_path');

            if ($assignment->attachment_path) {
                $files->push($assignment->attachment_path);
            }

            Storage::disk('local')->delete($files->all());
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Teacher, $this>
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * @return BelongsTo<Subject, $this>
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * @return BelongsTo<SchoolClass, $this>
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    /**
     * @return HasMany<AssignmentSubmission, $this>
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    /**
     * Assignments whose due date has not passed yet.
     *
     * @param  Builder<Assignment>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('due_at', '>=', now());
    }

    /**
     * Load `submissions_count` and `students_count` (active students in the class) in one query.
     *
     * @param  Builder<Assignment>  $query
     */
    public function scopeWithSubmissionCounts(Builder $query): void
    {
        $query->withCount('submissions')->addSelect([
            'students_count' => Student::query()
                ->selectRaw('count(*)')
                ->whereColumn('students.school_class_id', 'assignments.school_class_id')
                ->where('is_active', true),
        ]);
    }

    public function isActive(): bool
    {
        return $this->due_at->isFuture();
    }

    public function statusFor(?AssignmentSubmission $submission): SubmissionStatus
    {
        if ($submission === null) {
            return SubmissionStatus::NotSubmitted;
        }

        return $submission->submitted_at->greaterThan($this->due_at)
            ? SubmissionStatus::Late
            : SubmissionStatus::Submitted;
    }

    /**
     * Submission counts for the progress summary.
     *
     * Expects `students_count` and `submissions_count` to be loaded with withCount()
     * when listing many assignments; falls back to queries otherwise.
     *
     * @return array{students: int, submitted: int, not_submitted: int, rate: int}
     */
    public function submissionSummary(): array
    {
        $students = $this->students_count ?? $this->schoolClass->students()->where('is_active', true)->count();
        $submitted = $this->submissions_count ?? $this->submissions()->count();

        return [
            'students' => $students,
            'submitted' => $submitted,
            'not_submitted' => max($students - $submitted, 0),
            'rate' => $students > 0 ? (int) round($submitted / $students * 100) : 0,
        ];
    }
}
