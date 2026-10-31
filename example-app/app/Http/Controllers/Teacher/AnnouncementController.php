<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $filters = $request->only(['search', 'read']);

        $announcements = Announcement::visibleTo($user)
            ->with('author')
            ->when($filters['search'] ?? null, fn (Builder $q, string $search) => $q->where(fn (Builder $inner) => $inner
                ->where('title', 'like', "%{$search}%")
                ->orWhere('body', 'like', "%{$search}%")))
            ->when(($filters['read'] ?? null) === 'unread', fn (Builder $q) => $q->whereDoesntHave('readers', fn (Builder $r) => $r->whereKey($user->id)))
            ->latest('published_at')
            ->paginate(10)
            ->withQueryString();

        return view('teacher.announcements.index', [
            'announcements' => $announcements,
            'filters' => $filters,
            'readIds' => $user->readAnnouncements()->pluck('announcements.id'),
        ]);
    }

    public function show(Request $request, Announcement $announcement): View
    {
        $this->ensureVisible($request, $announcement);

        return view('teacher.announcements.show', [
            'announcement' => $announcement->load('author'),
            'isRead' => $announcement->isReadBy($request->user()),
        ]);
    }

    public function markAsRead(Request $request, Announcement $announcement): RedirectResponse
    {
        $this->ensureVisible($request, $announcement);

        $request->user()->readAnnouncements()->syncWithoutDetaching([
            $announcement->id => ['read_at' => now()],
        ]);

        return back()->with('success', __('Announcement marked as read.'));
    }

    private function ensureVisible(Request $request, Announcement $announcement): void
    {
        abort_unless(Announcement::visibleTo($request->user())->whereKey($announcement->id)->exists(), 404);
    }
}
