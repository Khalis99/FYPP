<?php

namespace App\Models;

use App\Enums\MaterialType;
use Database\Factories\TeachingMaterialFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['teacher_id', 'subject_id', 'school_class_id', 'title', 'topic', 'type', 'description', 'file_path', 'file_name', 'file_size'])]
class TeachingMaterial extends Model
{
    /** @use HasFactory<TeachingMaterialFactory> */
    use HasFactory;

    /**
     * Files are deleted from storage together with the record.
     */
    protected static function booted(): void
    {
        static::deleted(function (TeachingMaterial $material): void {
            Storage::disk('local')->delete($material->file_path);
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => MaterialType::class,
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
     * File size for display, e.g. "1.4 MB" (no intl extension required).
     */
    public function readableFileSize(): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = (float) $this->file_size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, $unit === 0 ? 0 : 1).' '.$units[$unit];
    }

    /**
     * Apply the search box and dropdown filters from the materials list page.
     *
     * @param  Builder<TeachingMaterial>  $query
     * @param  array{search?: string|null, subject_id?: string|int|null, school_class_id?: string|int|null, type?: string|null}  $filters
     */
    public function scopeFilter(Builder $query, array $filters): void
    {
        $query
            ->when($filters['search'] ?? null, fn (Builder $q, string $search) => $q->where(
                fn (Builder $inner) => $inner->where('title', 'like', "%{$search}%")->orWhere('topic', 'like', "%{$search}%")
            ))
            ->when($filters['subject_id'] ?? null, fn (Builder $q, $id) => $q->where('subject_id', $id))
            ->when($filters['school_class_id'] ?? null, fn (Builder $q, $id) => $q->where('school_class_id', $id))
            ->when($filters['type'] ?? null, fn (Builder $q, string $type) => $q->where('type', $type));
    }
}
