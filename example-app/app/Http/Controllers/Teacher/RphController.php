<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\GenerateRphRequest;
use App\Http\Requests\Teacher\RphDocumentRequest;
use App\Models\RphDocument;
use App\Models\Teacher;
use App\Services\Ai\AiGenerationException;
use App\Services\Rph\RphContent;
use App\Services\Rph\RphGenerationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * AI-assisted RPH flow:
 * create (form) → generate (AI) → draft (review & edit) → store → show / edit / print / download.
 *
 * The generated draft is kept in the session until the teacher saves it,
 * so nothing is written to the database without the teacher's review.
 */
class RphController extends Controller
{
    private const DRAFT_SESSION_KEY = 'rph_draft';

    /**
     * My RPH list.
     */
    public function index(Request $request): View
    {
        $teacher = $this->currentTeacher($request);
        $filters = $request->only(['search', 'subject_id', 'school_class_id', 'date']);

        return view('teacher.rph.index', [
            'documents' => $teacher->rphDocuments()
                ->filter($filters)
                ->with(['subject', 'schoolClass'])
                ->latest('updated_at')
                ->paginate(10)
                ->withQueryString(),
            'filters' => $filters,
            ...$this->formOptions($teacher),
        ]);
    }

    /**
     * The generator form. "?from=draft" refills it with the inputs of the current draft.
     */
    public function create(Request $request): View
    {
        $inputs = $request->query('from') === 'draft'
            ? $request->session()->get(self::DRAFT_SESSION_KEY.'.inputs', [])
            : [];

        return view('teacher.rph.create', [
            'inputs' => $inputs,
            ...$this->formOptions($this->currentTeacher($request)),
        ]);
    }

    public function generate(GenerateRphRequest $request, RphGenerationService $generator): RedirectResponse
    {
        try {
            $content = $generator->generate($request->forAi());
        } catch (AiGenerationException $exception) {
            return redirect()->route('teacher.rph.create')
                ->withInput()
                ->with('error', $exception->getMessage());
        }

        $request->session()->put(self::DRAFT_SESSION_KEY, [
            'inputs' => $request->validated(),
            'content' => $content,
            'provider' => $generator->providerName(),
        ]);

        return redirect()->route('teacher.rph.draft')
            ->with('success', 'RPH draft generated. Please review and edit it before saving.');
    }

    public function draft(Request $request): View|RedirectResponse
    {
        $draft = $request->session()->get(self::DRAFT_SESSION_KEY);

        if (! $draft) {
            return redirect()->route('teacher.rph.create');
        }

        $inputs = $draft['inputs'];
        $teacher = $this->currentTeacher($request);
        $options = $this->formOptions($teacher);

        $document = new RphDocument([
            'title' => 'RPH: '.$inputs['topic'].' ('.($options['classOptions'][$inputs['school_class_id']] ?? '').')',
            'subject_id' => $inputs['subject_id'],
            'school_class_id' => $inputs['school_class_id'],
            'topic' => $inputs['topic'],
            'theme' => $inputs['theme'] ?? null,
            'unit' => $inputs['unit'] ?? null,
            'lesson_date' => $inputs['lesson_date'],
            'week' => $inputs['week'] ?? null,
            'start_time' => $inputs['start_time'] ?? null,
            'duration_minutes' => $inputs['duration_minutes'],
            'student_count' => $inputs['student_count'] ?? null,
            'content' => $draft['content'],
        ]);

        return view('teacher.rph.draft', [
            'document' => $document,
            'inputs' => $inputs,
            ...$options,
        ]);
    }

    public function store(RphDocumentRequest $request): RedirectResponse
    {
        $draft = $request->session()->pull(self::DRAFT_SESSION_KEY);

        $document = $request->teacher()->rphDocuments()->create([
            ...$request->documentData(),
            'inputs' => $draft['inputs'] ?? null,
            'is_ai_generated' => $draft !== null,
            'ai_provider' => $draft['provider'] ?? null,
        ]);

        return redirect()->route('teacher.rph.show', $document)->with('success', 'RPH saved successfully.');
    }

    public function show(RphDocument $rph): View
    {
        Gate::authorize('view', $rph);

        return view('teacher.rph.show', ['document' => $rph->load(['subject', 'schoolClass', 'teacher.user'])]);
    }

    public function edit(Request $request, RphDocument $rph): View
    {
        Gate::authorize('update', $rph);

        return view('teacher.rph.edit', [
            'document' => $rph,
            ...$this->formOptions($this->currentTeacher($request)),
        ]);
    }

    public function update(RphDocumentRequest $request, RphDocument $rph): RedirectResponse
    {
        Gate::authorize('update', $rph);

        $rph->update($request->documentData());

        return redirect()->route('teacher.rph.show', $rph)->with('success', 'RPH saved successfully.');
    }

    public function destroy(RphDocument $rph): RedirectResponse
    {
        Gate::authorize('delete', $rph);

        $rph->delete();

        return redirect()->route('teacher.rph.index')->with('success', 'RPH deleted.');
    }

    /**
     * Clean, print-friendly page. Teachers can also "Save as PDF" from the print dialog.
     */
    public function print(RphDocument $rph): View
    {
        Gate::authorize('view', $rph);

        return view('teacher.rph.print', ['document' => $rph->load(['subject', 'schoolClass', 'teacher.user'])]);
    }

    /**
     * Download as a Word-compatible document (.doc) that teachers can edit offline.
     */
    public function download(RphDocument $rph): Response
    {
        Gate::authorize('view', $rph);

        $html = view('teacher.rph.word', ['document' => $rph->load(['subject', 'schoolClass', 'teacher.user'])])->render();
        $fileName = Str::slug($rph->title).'.doc';

        return response($html, 200, [
            'Content-Type' => 'application/msword',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }

    /**
     * @return array{subjectOptions: array<int, string>, classOptions: array<int, string>, sections: array<string, array{0: string, 1: bool, 2: string}>, languageOptions: array<string, string>}
     */
    private function formOptions(Teacher $teacher): array
    {
        return [
            'subjectOptions' => $teacher->subjectsTaught()->pluck('name', 'id')->all(),
            'classOptions' => $teacher->classesTaught()->pluck('name', 'id')->all(),
            'sections' => RphContent::SECTIONS,
            'languageOptions' => RphGenerationService::LANGUAGES,
        ];
    }
}
