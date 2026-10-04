<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                'teachers' => Teacher::count(),
                'students' => Student::where('is_active', true)->count(),
                'classes' => SchoolClass::count(),
                'assignments' => Assignment::count(),
                'announcements' => Announcement::published()->count(),
            ],
            'recentUsers' => User::latest()->take(5)->get(),
            'recentAnnouncements' => Announcement::latest()->take(5)->get(),
            'classes' => SchoolClass::query()
                ->withCount([
                    'students' => fn ($q) => $q->where('is_active', true),
                    'teachingLoads',
                    'assignments',
                ])
                ->orderBy('form_level')
                ->orderBy('name')
                ->get(),
        ]);
    }
}
