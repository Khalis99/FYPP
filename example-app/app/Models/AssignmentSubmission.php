<?php

namespace App\Models;

use App\Enums\SubmissionStatus;
use App\Notifications\SubmissionReceived;
use Database\Factories\AssignmentSubmissionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['assignment_id', 'student_id', 'submitted_at', 'file_path', 'file_name', 'remarks'])]
class AssignmentSubmission extends Model
{
    /** @use HasFactory<AssignmentSubmissionFactory> */
    use HasFactory;

    /**
     * Tell the teacher about new submissions, unless the teacher recorded it themselves.
     */
    protected static function booted(): void
    {
        static::created(function (AssignmentSubmission $submission): void {
            $teacherUser = $submission->assignment->teacher->user;

            if (auth()->id() !== $teacherUser->id) {
                $teacherUser->notify(new SubmissionReceived($submission));
            }
        });

        static::deleted(function (AssignmentSubmission $submission): void {
            if ($submission->file_path) {
                Storage::disk('local')->delete($submission->file_path);
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Assignment, $this>
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function status(): SubmissionStatus
    {
        return $this->assignment->statusFor($this);
    }
}
