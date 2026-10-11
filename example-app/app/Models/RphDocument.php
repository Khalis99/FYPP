<?php

namespace App\Models;

use Database\Factories\RphDocumentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A saved Rancangan Pengajaran Harian (daily lesson plan).
 *
 * The `content` column holds the structured sections described in RphContent::SECTIONS.
 */
#[Fillable(['teacher_id', 'subject_id', 'school_class_id', 'title', 'topic', 'theme', 'unit', 'lesson_date', 'week', 'start_time', 'duration_minutes', 'student_count', 'content', 'inputs', 'is_ai_generated', 'ai_provider'])]
class RphDocument extends Model
{
    /** @use HasFactory<RphDocumentFactory> */
    use HasFactory;

    /**
     * Lesson time for the "Masa" box, e.g. "8:00 AM – 9:00 AM", or null when no start time is set.
     */
    public function timeRange(): ?string
    {
        if (! $this->start_time) {
            return null;
        }

        $start = Carbon::parse($this->start_time);

        return $start->format('g:i A').' – '.$start->copy()->addMinutes($this->duration_minutes)->format('g:i A');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'lesson_date' => 'date',
            'content' => 'array',
            'inputs' => 'array',
            'is_ai_generated' => 'boolean',
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
     * @param  Builder<RphDocument>  $query
     * @param  array{search?: string|null, subject_id?: string|int|null, school_class_id?: string|int|null, date?: string|null}  $filters
     */
    public function scopeFilter(Builder $query, array $filters): void
    {
        $query
            ->when($filters['search'] ?? null, fn (Builder $q, string $search) => $q->where(
                fn (Builder $inner) => $inner->where('title', 'like', "%{$search}%")->orWhere('topic', 'like', "%{$search}%")
            ))
            ->when($filters['subject_id'] ?? null, fn (Builder $q, $id) => $q->where('subject_id', $id))
            ->when($filters['school_class_id'] ?? null, fn (Builder $q, $id) => $q->where('school_class_id', $id))
            ->when($filters['date'] ?? null, fn (Builder $q, string $date) => $q->whereDate('lesson_date', $date));
    }
}
