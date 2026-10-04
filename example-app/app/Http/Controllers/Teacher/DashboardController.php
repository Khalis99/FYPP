<?php

namespace App\Http\Controllers\Teacher;

use App\Enums\Weekday;
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $teacher = $this->currentTeacher($request);
        $user = $request->user();

        $activeAssignments = $teacher->assignments()
            ->active()
            ->withSubmissionCounts()
            ->with(['schoolClass', 'subject'])
            ->orderBy('due_at')
            ->get();

        $today = Weekday::fromDate(now());

        return view('teacher.dashboard', [
            'teacher' => $teacher,
            'stats' => [
                'classes' => $teacher->classIds()->count(),
                'materials' => $teacher->materials()->count(),
                'activeAssignments' => $activeAssignments->count(),
                'pendingSubmissions' => $activeAssignments->sum(fn ($assignment) => $assignment->submissionSummary()['not_submitted']),
            ],
            'today' => $today,
            'todaySchedule' => $today
                ? $teacher->timetables()->where('day', $today)->with(['subject', 'schoolClass'])->orderBy('start_time')->get()
                : collect(),
            'announcements' => Announcement::visibleTo($user)->latest('published_at')->take(4)->get(),
            'readAnnouncementIds' => $user->readAnnouncements()->pluck('announcements.id'),
            'upcomingAssignments' => $activeAssignments->take(5),
        ]);
    }
}
