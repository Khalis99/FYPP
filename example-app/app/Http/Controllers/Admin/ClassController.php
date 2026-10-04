<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SchoolClassRequest;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ClassController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'academic_year']);

        $classes = SchoolClass::query()
            ->withCount(['students' => fn ($q) => $q->where('is_active', true)])
            ->with(['teachingLoads.subject', 'teachingLoads.teacher.user'])
            ->when($filters['search'] ?? null, fn (Builder $q, string $search) => $q->where('name', 'like', "%{$search}%"))
            ->when($filters['academic_year'] ?? null, fn (Builder $q, string $year) => $q->where('academic_year', $year))
            ->orderByDesc('academic_year')
            ->orderBy('form_level')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.classes.index', [
            'classes' => $classes,
            'filters' => $filters,
            'yearOptions' => SchoolClass::distinct()->orderByDesc('academic_year')->pluck('academic_year', 'academic_year')->all(),
        ]);
    }

    public function create(): View
    {
        return view('admin.classes.create', [
            'schoolClass' => new SchoolClass(['academic_year' => (string) now()->year]),
            ...$this->assignmentOptions(),
        ]);
    }

    public function store(SchoolClassRequest $request): RedirectResponse
    {
        $schoolClass = DB::transaction(function () use ($request): SchoolClass {
            $schoolClass = SchoolClass::create($request->safe()->only(['name', 'form_level', 'academic_year']));

            if ($request->filled('subject_id')) {
                $schoolClass->teachingLoads()->create($request->safe()->only(['subject_id', 'teacher_id']));
            }

            return $schoolClass;
        });

        return redirect()->route('admin.classes.show', $schoolClass)->with('success', 'Class created.');
    }

    public function show(SchoolClass $schoolClass): View
    {
        return view('admin.classes.show', [
            'schoolClass' => $schoolClass,
            'students' => $schoolClass->students()->orderBy('name')->get(),
            'teachingLoads' => $schoolClass->teachingLoads()->with(['subject', 'teacher.user'])->get(),
            'availableStudents' => Student::query()
                ->where('is_active', true)
                ->where(fn (Builder $q) => $q->whereNull('school_class_id')->orWhere('school_class_id', '!=', $schoolClass->id))
                ->with('schoolClass')
                ->orderBy('name')
                ->get(),
            ...$this->assignmentOptions(),
        ]);
    }

    public function edit(SchoolClass $schoolClass): View
    {
        return view('admin.classes.edit', ['schoolClass' => $schoolClass]);
    }

    public function update(SchoolClassRequest $request, SchoolClass $schoolClass): RedirectResponse
    {
        $schoolClass->update($request->safe()->only(['name', 'form_level', 'academic_year']));

        return redirect()->route('admin.classes.show', $schoolClass)->with('success', 'Class updated.');
    }

    public function destroy(SchoolClass $schoolClass): RedirectResponse
    {
        $schoolClass->delete();

        return redirect()->route('admin.classes.index')->with('success', 'Class deleted. Its students are now without a class.');
    }

    /**
     * Move the selected students into this class.
     */
    public function addStudents(Request $request, SchoolClass $schoolClass): RedirectResponse
    {
        $validated = $request->validate([
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['integer', 'exists:students,id'],
        ], ['student_ids.required' => 'Please select at least one student.']);

        $count = Student::whereIn('id', $validated['student_ids'])->update(['school_class_id' => $schoolClass->id]);

        return back()->with('success', "{$count} student(s) added to {$schoolClass->name}.");
    }

    public function removeStudent(SchoolClass $schoolClass, Student $student): RedirectResponse
    {
        $student->update(['school_class_id' => null]);

        return back()->with('success', "{$student->name} removed from {$schoolClass->name}.");
    }

    /**
     * @return array{subjectOptions: array<int, string>, teacherOptions: array<int, string>}
     */
    private function assignmentOptions(): array
    {
        return [
            'subjectOptions' => Subject::orderBy('name')->pluck('name', 'id')->all(),
            'teacherOptions' => Teacher::query()
                ->with('user')
                ->get()
                ->filter(fn (Teacher $teacher) => $teacher->user->is_active)
                ->sortBy('user.name')
                ->mapWithKeys(fn (Teacher $teacher) => [$teacher->id => $teacher->user->name.' ('.$teacher->teacher_code.')'])
                ->all(),
        ];
    }
}
