<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AssignTeacherRequest;
use App\Models\ClassTeacher;
use App\Models\SchoolClass;
use Illuminate\Http\RedirectResponse;

/**
 * Assign (or re-assign) the teacher for a subject in a class.
 */
class ClassTeacherController extends Controller
{
    public function store(AssignTeacherRequest $request, SchoolClass $schoolClass): RedirectResponse
    {
        $schoolClass->teachingLoads()->updateOrCreate(
            ['subject_id' => $request->validated('subject_id')],
            ['teacher_id' => $request->validated('teacher_id')],
        );

        return back()->with('success', __('Teacher assigned.'));
    }

    public function destroy(SchoolClass $schoolClass, ClassTeacher $classTeacher): RedirectResponse
    {
        abort_unless($classTeacher->school_class_id === $schoolClass->id, 404);

        $classTeacher->delete();

        return back()->with('success', __('Teacher removed from this subject.'));
    }
}
