<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AnnouncementAudience;
use App\Enums\AnnouncementStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AnnouncementRequest;
use App\Models\Announcement;
use App\Models\User;
use App\Notifications\AnnouncementPublished;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'status']);

        $announcements = Announcement::query()
            ->with('author')
            ->withCount('readers')
            ->when($filters['search'] ?? null, fn (Builder $q, string $search) => $q->where('title', 'like', "%{$search}%"))
            ->when($filters['status'] ?? null, fn (Builder $q, string $status) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.announcements.index', [
            'announcements' => $announcements,
            'filters' => $filters,
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function create(): View
    {
        return view('admin.announcements.create', [
            'announcement' => new Announcement(['audience' => AnnouncementAudience::All]),
            'audienceOptions' => $this->audienceOptions(),
        ]);
    }

    public function store(AnnouncementRequest $request): RedirectResponse
    {
        $announcement = $request->user()->announcementsAuthored()->create($request->validated());

        if ($announcement->isPublished()) {
            $this->notifyAudience($announcement);
        }

        return redirect()->route('admin.announcements.show', $announcement)
            ->with('success', $announcement->isPublished() ? __('Announcement published.') : __('Announcement saved as draft.'));
    }

    public function show(Announcement $announcement): View
    {
        return view('admin.announcements.show', [
            'announcement' => $announcement->load('author')->loadCount('readers'),
        ]);
    }

    public function edit(Announcement $announcement): View
    {
        return view('admin.announcements.edit', [
            'announcement' => $announcement,
            'audienceOptions' => $this->audienceOptions(),
        ]);
    }

    public function update(AnnouncementRequest $request, Announcement $announcement): RedirectResponse
    {
        $wasPublished = $announcement->isPublished();

        $announcement->update($request->validated());

        if (! $wasPublished && $announcement->isPublished()) {
            $this->notifyAudience($announcement);
        }

        return redirect()->route('admin.announcements.show', $announcement)->with('success', __('Announcement updated.'));
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        return redirect()->route('admin.announcements.index')->with('success', __('Announcement deleted.'));
    }

    /**
     * Quick "Publish" button for drafts.
     */
    public function publish(Announcement $announcement): RedirectResponse
    {
        if (! $announcement->isPublished()) {
            $announcement->update(['status' => AnnouncementStatus::Published]);
            $this->notifyAudience($announcement);
        }

        return redirect()->route('admin.announcements.show', $announcement)->with('success', __('Announcement published.'));
    }

    /**
     * Stamp the publish date and notify everyone in the target audience.
     */
    private function notifyAudience(Announcement $announcement): void
    {
        $announcement->update(['published_at' => now()]);

        $recipients = User::query()
            ->where('is_active', true)
            ->whereKeyNot($announcement->user_id)
            ->whereIn('role', match ($announcement->audience) {
                AnnouncementAudience::All => [UserRole::Teacher, UserRole::Admin],
                AnnouncementAudience::Teachers => [UserRole::Teacher],
                AnnouncementAudience::Admins => [UserRole::Admin],
            })
            ->get();

        Notification::send($recipients, new AnnouncementPublished($announcement));
    }

    /**
     * @return array<string, string>
     */
    private function audienceOptions(): array
    {
        return collect(AnnouncementAudience::cases())->mapWithKeys(fn (AnnouncementAudience $a) => [$a->value => $a->label()])->all();
    }

    /**
     * @return array<string, string>
     */
    private function statusOptions(): array
    {
        return collect(AnnouncementStatus::cases())->mapWithKeys(fn (AnnouncementStatus $s) => [$s->value => $s->label()])->all();
    }
}
