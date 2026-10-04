<?php

namespace App\Http\Controllers\Teacher;

use App\Enums\SubmissionStatus;
use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Read-only student records for the classes a teacher teaches.
 */
class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $teacher = $this->currentTeacher($request);
        $classIds = $teacher->classIds();
        $filters = $request->only(['search', 'school_class_id']);

        $students = Student::query()
            ->whereIn('school_class_id', $classIds)
            ->where('is_active', true)
            ->with('schoolClass')
            ->when($filters['search'] ?? null, fn (Builder $q, string $search) => $q->where(fn (Builder $inner) => $inner
                ->where('name', 'like', "%{$search}%")
                ->orWhere('student_code', 'like', "%{$search}%")))
            ->when($filters['school_class_id'] ?? null, fn (Builder $q, $id) => $q->where('school_class_id', $id))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('teacher.students.index', [
            'students' => $students,
            'filters' => $filters,
            'classOptions' => $teacher->classesTaught()->pluck('name', 'id')->all(),
        ]);
    }

    /**
     * Student profile with submission performance on this teacher's assignments.
     */
    public function show(Request $request, Student $student): View
    {
        Gate::authorize('view', $student);

        $teacher = $this->currentTeacher($request);
        $student->load('schoolClass');

        $submissions = $student->submissions()->get()->keyBy('assignment_id');

        $assignments = $teacher->assignments()
            ->where('school_class_id', $student->school_class_id)
            ->with('subject')
            ->orderByDesc('due_at')
            ->get()
            ->map(fn ($assignment) => [
                'assignment' => $assignment,
                'submission' => $submission = $submissions->get($assignment->id),
                // Assignments not yet due and not submitted are "pending", not missing.
                'status' => $submission || ! $assignment->isActive() ? $assignment->statusFor($submission) : null,
            ]);

        $performance = $assignments->groupBy(fn ($row) => $row['assignment']->subject->name)
            ->map(function ($rows) {
                $due = $rows->whereNotNull('status');
                $onTime = $due->where('status', SubmissionStatus::Submitted)->count();
                $late = $due->where('status', SubmissionStatus::Late)->count();

                return [
                    'total' => $due->count(),
                    'on_time' => $onTime,
                    'late' => $late,
                    'missing' => $due->where('status', SubmissionStatus::NotSubmitted)->count(),
                    'rate' => $due->count() > 0 ? (int) round(($onTime + $late) / $due->count() * 100) : null,
                ];
            });

        return view('teacher.students.show', compact('student', 'assignments', 'performance'));
    }
}
