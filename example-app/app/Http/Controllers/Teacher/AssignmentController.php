<?php

namespace App\Http\Controllers\Teacher;

use App\Enums\SubmissionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\AssignmentRequest;
use App\Models\Assignment;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssignmentController extends Controller
{
    public function index(Request $request): View
    {
        $teacher = $this->currentTeacher($request);
        $filters = $request->only(['search', 'school_class_id', 'status']);

        $assignments = $teacher->assignments()
            ->withSubmissionCounts()
            ->with(['schoolClass', 'subject'])
            ->when($filters['search'] ?? null, fn (Builder $q, string $search) => $q->where('title', 'like', "%{$search}%"))
            ->when($filters['school_class_id'] ?? null, fn (Builder $q, $id) => $q->where('school_class_id', $id))
            ->when(($filters['status'] ?? null) === 'active', fn (Builder $q) => $q->active())
            ->when(($filters['status'] ?? null) === 'closed', fn (Builder $q) => $q->where('due_at', '<', now()))
            ->orderByDesc('due_at')
            ->paginate(10)
            ->withQueryString();

        return view('teacher.assignments.index', [
            'assignments' => $assignments,
            'filters' => $filters,
            ...$this->formOptions($teacher),
        ]);
    }

    public function create(Request $request): View
    {
        return view('teacher.assignments.create', [
            'assignment' => new Assignment(['school_class_id' => $request->integer('school_class_id') ?: null]),
            ...$this->formOptions($this->currentTeacher($request)),
        ]);
    }

    public function store(AssignmentRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('attachment');

        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $request->file('attachment')->store('assignments', 'local');
            $data['attachment_name'] = $request->file('attachment')->getClientOriginalName();
        }

        $assignment = $request->teacher()->assignments()->create($data);

        return redirect()->route('teacher.assignments.show', $assignment)->with('success', 'Assignment created successfully.');
    }

    /**
     * Assignment details plus every student in the class and their submission status.
     */
    public function show(Request $request, Assignment $assignment): View
    {
        Gate::authorize('view', $assignment);

        $assignment->load(['schoolClass', 'subject']);
        $submissions = $assignment->submissions()->get()->keyBy('student_id');

        $rows = $assignment->schoolClass->students()->where('is_active', true)->orderBy('name')->get()
            ->map(fn ($student) => [
                'student' => $student,
                'submission' => $submission = $submissions->get($student->id),
                'status' => $assignment->statusFor($submission),
            ]);

        $statusFilter = SubmissionStatus::tryFrom((string) $request->query('status'));

        return view('teacher.assignments.show', [
            'assignment' => $assignment,
            'summary' => [
                'students' => $rows->count(),
                'submitted' => $rows->where('submission', '!=', null)->count(),
                'late' => $rows->where('status', SubmissionStatus::Late)->count(),
                'not_submitted' => $rows->where('status', SubmissionStatus::NotSubmitted)->count(),
            ],
            'rows' => $statusFilter ? $rows->where('status', $statusFilter) : $rows,
            'statusFilter' => $statusFilter,
            'pendingStudents' => $rows->where('submission', null)->pluck('student'),
        ]);
    }

    public function edit(Request $request, Assignment $assignment): View
    {
        Gate::authorize('update', $assignment);

        return view('teacher.assignments.edit', [
            'assignment' => $assignment,
            ...$this->formOptions($this->currentTeacher($request)),
        ]);
    }

    public function update(AssignmentRequest $request, Assignment $assignment): RedirectResponse
    {
        Gate::authorize('update', $assignment);

        $data = $request->safe()->except('attachment');

        if ($request->hasFile('attachment')) {
            if ($assignment->attachment_path) {
                Storage::disk('local')->delete($assignment->attachment_path);
            }

            $data['attachment_path'] = $request->file('attachment')->store('assignments', 'local');
            $data['attachment_name'] = $request->file('attachment')->getClientOriginalName();
        }

        $assignment->update($data);

        return redirect()->route('teacher.assignments.show', $assignment)->with('success', 'Assignment updated.');
    }

    public function destroy(Assignment $assignment): RedirectResponse
    {
        Gate::authorize('delete', $assignment);

        $assignment->delete();

        return redirect()->route('teacher.assignments.index')->with('success', 'Assignment deleted.');
    }

    public function attachment(Assignment $assignment): StreamedResponse
    {
        Gate::authorize('view', $assignment);

        abort_unless($assignment->attachment_path && Storage::disk('local')->exists($assignment->attachment_path), 404);

        return Storage::disk('local')->download($assignment->attachment_path, $assignment->attachment_name);
    }

    /**
     * @return array{subjectOptions: array<int, string>, classOptions: array<int, string>}
     */
    private function formOptions(Teacher $teacher): array
    {
        return [
            'subjectOptions' => $teacher->subjectsTaught()->pluck('name', 'id')->all(),
            'classOptions' => $teacher->classesTaught()->pluck('name', 'id')->all(),
        ];
    }
}
