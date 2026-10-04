<?php

namespace App\Models;

use App\Enums\Weekday;
use Database\Factories\TimetableFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

#[Fillable(['teacher_id', 'subject_id', 'school_class_id', 'day', 'start_time', 'end_time', 'room'])]
class Timetable extends Model
{
    /** @use HasFactory<TimetableFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'day' => Weekday::class,
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
     * Entries on the same day whose time range overlaps [start, end).
     *
     * @param  Builder<Timetable>  $query
     */
    public function scopeOverlapping(Builder $query, int $day, string $start, string $end): void
    {
        $query->where('day', $day)
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start);
    }

    /**
     * Human-friendly time range, e.g. "8:00 AM – 9:00 AM".
     */
    public function timeRange(): string
    {
        return Carbon::parse($this->start_time)->format('g:i A').' – '.Carbon::parse($this->end_time)->format('g:i A');
    }
}
