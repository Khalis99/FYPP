<?php

namespace App\Models;

use Database\Factories\TeacherFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

#[Fillable(['user_id', 'department'])]
class Teacher extends Model
{
    /** @use HasFactory<TeacherFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Each row is one "teaching load": this teacher teaches a subject to a class.
     *
     * @return HasMany<ClassTeacher, $this>
     */
    public function teachingLoads(): HasMany
    {
        return $this->hasMany(ClassTeacher::class);
    }

    /**
     * @return BelongsToMany<SchoolClass, $this, ClassTeacher>
     */
    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'class_teacher')
            ->using(ClassTeacher::class)
            ->withPivot(['id', 'subject_id'])
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany<Subject, $this, ClassTeacher>
     */
    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'class_teacher')
            ->using(ClassTeacher::class)
            ->withPivot(['id', 'school_class_id']);
    }

    /**
     * @return HasMany<TeachingMaterial, $this>
     */
    public function materials(): HasMany
    {
        return $this->hasMany(TeachingMaterial::class);
    }

    /**
     * @return HasMany<Assignment, $this>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    /**
     * @return HasMany<RphDocument, $this>
     */
    public function rphDocuments(): HasMany
    {
        return $this->hasMany(RphDocument::class);
    }

    /**
     * @return HasMany<Timetable, $this>
     */
    public function timetables(): HasMany
    {
        return $this->hasMany(Timetable::class);
    }

    /**
     * IDs of every class this teacher teaches (any subject).
     *
     * @return Collection<int, int>
     */
    public function classIds(): Collection
    {
        return $this->teachingLoads()->pluck('school_class_id')->unique()->values();
    }

    public function teachesClass(int $schoolClassId): bool
    {
        return $this->teachingLoads()->where('school_class_id', $schoolClassId)->exists();
    }

    public function teachesSubjectInClass(int $subjectId, int $schoolClassId): bool
    {
        return $this->teachingLoads()
            ->where('school_class_id', $schoolClassId)
            ->where('subject_id', $subjectId)
            ->exists();
    }

    /**
     * Distinct subjects this teacher teaches, for profile display and form dropdowns.
     *
     * @return Collection<int, Subject>
     */
    public function subjectsTaught(): Collection
    {
        return $this->subjects()->orderBy('name')->get()->unique('id')->values();
    }

    /**
     * Distinct classes this teacher teaches, for form dropdowns.
     *
     * @return Collection<int, SchoolClass>
     */
    public function classesTaught(): Collection
    {
        return $this->classes()->orderBy('name')->get()->unique('id')->values();
    }
}
