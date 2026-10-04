<?php

namespace App\Models;

use App\Enums\AnnouncementAudience;
use App\Enums\AnnouncementStatus;
use Database\Factories\AnnouncementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['user_id', 'title', 'body', 'audience', 'status', 'published_at'])]
class Announcement extends Model
{
    /** @use HasFactory<AnnouncementFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'audience' => AnnouncementAudience::class,
            'status' => AnnouncementStatus::class,
            'published_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function readers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'announcement_reads')->withPivot('read_at');
    }

    /**
     * @param  Builder<Announcement>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', AnnouncementStatus::Published)
            ->where('published_at', '<=', now());
    }

    /**
     * Published announcements the given user is allowed to see.
     *
     * @param  Builder<Announcement>  $query
     */
    public function scopeVisibleTo(Builder $query, User $user): void
    {
        $query->published()->whereIn('audience', AnnouncementAudience::visibleTo($user->role));
    }

    public function isPublished(): bool
    {
        return $this->status === AnnouncementStatus::Published;
    }

    public function isReadBy(User $user): bool
    {
        return $this->readers()->whereKey($user->id)->exists();
    }
}
