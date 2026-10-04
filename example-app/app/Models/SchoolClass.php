<?php

namespace App\Models;

use Database\Factories\SchoolClassFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'form_level', 'academic_year'])]
class SchoolClass extends Model
{
    /** @use HasFactory<SchoolClassFactory> */
    use HasFactory;

    /**
     * @return HasMany<Student, $this>
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    /**
     * @return HasMany<ClassTeacher, $this>
     */
    public function teachingLoads(): HasMany
    {
        return $this->hasMany(ClassTeacher::class);
    }

    /**
     * @return BelongsToMany<Teacher, $this, ClassTeacher>
     */
    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(Teacher::class, 'class_teacher')
            ->using(ClassTeacher::class)
            ->withPivot(['id', 'subject_id'])
            ->withTimestamps();
    }

    /**
     * @return HasMany<Assignment, $this>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    /**
     * @return HasMany<TeachingMaterial, $this>
     */
    public function materials(): HasMany
    {
        return $this->hasMany(TeachingMaterial::class);
    }

    /**
     * @return HasMany<Timetable, $this>
     */
    public function timetables(): HasMany
    {
        return $this->hasMany(Timetable::class);
    }
}
