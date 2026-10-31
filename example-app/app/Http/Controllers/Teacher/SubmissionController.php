<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\RecordSubmissionRequest;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubmissionController extends Controller
{
    public function store(RecordSubmissionRequest $request, Assignment $assignment): RedirectResponse
    {
        $data = $request->safe()->except('file');

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('submissions', 'local');
            $data['file_name'] = $request->file('file')->getClientOriginalName();
        }

        $assignment->submissions()->create($data);

        return back()->with('success', __('Submission recorded.'));
    }

    public function download(AssignmentSubmission $submission): StreamedResponse
    {
        Gate::authorize('view', $submission->assignment);

        abort_unless($submission->file_path && Storage::disk('local')->exists($submission->file_path), 404);

        return Storage::disk('local')->download($submission->file_path, $submission->file_name);
    }

    /**
     * Remove a submission recorded by mistake.
     */
    public function destroy(AssignmentSubmission $submission): RedirectResponse
    {
        Gate::authorize('update', $submission->assignment);

        $submission->delete();

        return back()->with('success', __('Submission removed.'));
    }
}
