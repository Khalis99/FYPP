<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ClassController extends Controller
{
    /**
     * Each card is one teaching load (a class + subject this teacher teaches).
     */
    public function index(Request $request): View
    {
        $teacher = $this->currentTeacher($request);
        $search = $request->string('search')->trim()->value();

        $loads = $teacher->teachingLoads()
            ->with(['schoolClass' => fn ($query) => $query->withCount(['students' => fn ($q) => $q->where('is_active', true)]), 'subject'])
            ->when($search, fn (Builder $query) => $query->where(fn (Builder $inner) => $inner
                ->whereHas('schoolClass', fn (Builder $q) => $q->where('name', 'like', "%{$search}%"))
                ->orWhereHas('subject', fn (Builder $q) => $q->where('name', 'like', "%{$search}%"))))
            ->get()
            ->sortBy(fn ($load) => $load->schoolClass->name.$load->subject->name);

        $schedule = $teacher->timetables()->orderBy('day')->orderBy('start_time')->get()
            ->groupBy(fn ($entry) => $entry->school_class_id.'-'.$entry->subject_id);

        return view('teacher.classes.index', compact('loads', 'schedule', 'search'));
    }

    public function show(Request $request, SchoolClass $schoolClass): View
    {
        Gate::authorize('view', $schoolClass);

        $teacher = $this->currentTeacher($request);

        return view('teacher.classes.show', [
            'schoolClass' => $schoolClass,
            'students' => $schoolClass->students()->where('is_active', true)->orderBy('name')->get(),
            'teachingLoads' => $schoolClass->teachingLoads()->with(['teacher.user', 'subject'])->get(),
            'assignments' => $teacher->assignments()->where('school_class_id', $schoolClass->id)
                ->withSubmissionCounts()->with('subject')->latest('due_at')->take(5)->get(),
            'materials' => $teacher->materials()->where('school_class_id', $schoolClass->id)
                ->with('subject')->latest()->take(5)->get(),
            'schedule' => $schoolClass->timetables()->with(['subject', 'teacher.user'])->orderBy('day')->orderBy('start_time')->get(),
        ]);
    }
}
