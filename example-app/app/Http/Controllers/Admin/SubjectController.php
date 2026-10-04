<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SubjectRequest;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(): View
    {
        return view('admin.subjects.index', [
            'subjects' => Subject::withCount('teachingLoads')->orderBy('name')->get(),
        ]);
    }

    public function store(SubjectRequest $request): RedirectResponse
    {
        Subject::create($request->validated());

        return back()->with('success', 'Subject added.');
    }

    public function update(SubjectRequest $request, Subject $subject): RedirectResponse
    {
        $subject->update($request->validated());

        return back()->with('success', 'Subject updated.');
    }

    /**
     * Subjects in use are protected because deleting them would also delete
     * teachers' materials and assignments for that subject.
     */
    public function destroy(Subject $subject): RedirectResponse
    {
        if ($subject->teachingLoads()->exists()) {
            return back()->with('error', "\"{$subject->name}\" is still assigned to classes. Remove those assignments first.");
        }

        $subject->delete();

        return back()->with('success', 'Subject deleted.');
    }
}
