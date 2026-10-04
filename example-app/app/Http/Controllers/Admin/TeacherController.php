<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TeacherRequest;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TeacherController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'department', 'status']);

        $teachers = Teacher::query()
            ->with('user')
            ->join('users', 'users.id', '=', 'teachers.user_id')
            ->select('teachers.*')
            ->when($filters['search'] ?? null, fn (Builder $q, string $search) => $q->where(fn (Builder $inner) => $inner
                ->where('users.name', 'like', "%{$search}%")
                ->orWhere('users.email', 'like', "%{$search}%")
                ->orWhere('teachers.teacher_code', 'like', "%{$search}%")))
            ->when($filters['department'] ?? null, fn (Builder $q, string $department) => $q->where('teachers.department', $department))
            ->when(($filters['status'] ?? null) === 'active', fn (Builder $q) => $q->where('users.is_active', true))
            ->when(($filters['status'] ?? null) === 'inactive', fn (Builder $q) => $q->where('users.is_active', false))
            ->orderBy('users.name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.teachers.index', [
            'teachers' => $teachers,
            'filters' => $filters,
            'departmentOptions' => Teacher::whereNotNull('department')->distinct()->orderBy('department')->pluck('department', 'department')->all(),
        ]);
    }

    public function create(): View
    {
        return view('admin.teachers.create', ['teacher' => new Teacher, 'user' => new User]);
    }

    public function store(TeacherRequest $request): RedirectResponse
    {
        $teacher = DB::transaction(function () use ($request): Teacher {
            $user = User::create([...$request->accountData(), 'role' => UserRole::Teacher]);

            return $user->teacher()->create($request->safe()->only(['teacher_code', 'department']));
        });

        return redirect()->route('admin.teachers.show', $teacher)->with('success', 'Teacher account created. You can now assign classes from the Classes page.');
    }

    public function show(Teacher $teacher): View
    {
        return view('admin.teachers.show', [
            'teacher' => $teacher->load('user'),
            'teachingLoads' => $teacher->teachingLoads()->with(['schoolClass', 'subject'])->get(),
            'timetables' => $teacher->timetables()->with(['schoolClass', 'subject'])->orderBy('day')->orderBy('start_time')->get(),
            'counts' => [
                'materials' => $teacher->materials()->count(),
                'assignments' => $teacher->assignments()->count(),
                'rph' => $teacher->rphDocuments()->count(),
            ],
        ]);
    }

    public function edit(Teacher $teacher): View
    {
        return view('admin.teachers.edit', ['teacher' => $teacher, 'user' => $teacher->user]);
    }

    public function update(TeacherRequest $request, Teacher $teacher): RedirectResponse
    {
        DB::transaction(function () use ($request, $teacher): void {
            $teacher->user->update($request->accountData());
            $teacher->update($request->safe()->only(['teacher_code', 'department']));
        });

        return redirect()->route('admin.teachers.show', $teacher)->with('success', 'Teacher details updated.');
    }
}
