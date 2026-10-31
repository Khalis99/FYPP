<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StudentRequest;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'school_class_id', 'status']);

        $students = Student::query()
            ->with('schoolClass')
            ->when($filters['search'] ?? null, fn (Builder $q, string $search) => $q->where(fn (Builder $inner) => $inner
                ->where('name', 'like', "%{$search}%")
                ->orWhere('student_code', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->when($filters['school_class_id'] ?? null, fn (Builder $q, $id) => $id === 'none' ? $q->whereNull('school_class_id') : $q->where('school_class_id', $id))
            ->when(($filters['status'] ?? null) === 'active', fn (Builder $q) => $q->where('is_active', true))
            ->when(($filters['status'] ?? null) === 'inactive', fn (Builder $q) => $q->where('is_active', false))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.students.index', [
            'students' => $students,
            'filters' => $filters,
            'classOptions' => ['none' => 'No class assigned'] + $this->classOptions(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.students.create', [
            'student' => new Student(['is_active' => true, 'school_class_id' => $request->integer('school_class_id') ?: null]),
            'classOptions' => $this->classOptions(),
        ]);
    }

    public function store(StudentRequest $request): RedirectResponse
    {
        $student = Student::create($request->validated());

        return redirect()->route('admin.students.show', $student)->with('success', __('Student record created.'));
    }

    public function show(Student $student): View
    {
        return view('admin.students.show', [
            'student' => $student->load('schoolClass'),
            'submissionCount' => $student->submissions()->count(),
        ]);
    }

    public function edit(Student $student): View
    {
        return view('admin.students.edit', ['student' => $student, 'classOptions' => $this->classOptions()]);
    }

    public function update(StudentRequest $request, Student $student): RedirectResponse
    {
        $student->update($request->validated());

        return redirect()->route('admin.students.show', $student)->with('success', __('Student record updated.'));
    }

    public function destroy(Student $student): RedirectResponse
    {
        $student->delete();

        return redirect()->route('admin.students.index')->with('success', __('Student record deleted.'));
    }

    /**
     * @return array<int, string>
     */
    private function classOptions(): array
    {
        return SchoolClass::orderBy('form_level')->orderBy('name')->get()
            ->mapWithKeys(fn (SchoolClass $class) => [$class->id => $class->name.' ('.$class->academic_year.')'])
            ->all();
    }
}
