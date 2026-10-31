<?php

namespace App\Http\Controllers\Teacher;

use App\Enums\MaterialType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\TeachingMaterialRequest;
use App\Models\Teacher;
use App\Models\TeachingMaterial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TeachingMaterialController extends Controller
{
    public function index(Request $request): View
    {
        $teacher = $this->currentTeacher($request);
        $filters = $request->only(['search', 'subject_id', 'school_class_id', 'type']);

        return view('teacher.materials.index', [
            'materials' => $teacher->materials()
                ->filter($filters)
                ->with(['subject', 'schoolClass'])
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'filters' => $filters,
            ...$this->formOptions($teacher),
        ]);
    }

    public function create(Request $request): View
    {
        return view('teacher.materials.create', [
            'material' => new TeachingMaterial(['school_class_id' => $request->integer('school_class_id') ?: null]),
            ...$this->formOptions($this->currentTeacher($request)),
        ]);
    }

    public function store(TeachingMaterialRequest $request): RedirectResponse
    {
        $file = $request->file('file');

        $material = $request->teacher()->materials()->create([
            ...$request->safe()->except('file'),
            'file_path' => $file->store('materials', 'local'),
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
        ]);

        return redirect()->route('teacher.materials.show', $material)->with('success', __('Teaching material uploaded successfully.'));
    }

    public function show(TeachingMaterial $material): View
    {
        Gate::authorize('view', $material);

        return view('teacher.materials.show', ['material' => $material->load(['subject', 'schoolClass'])]);
    }

    public function edit(Request $request, TeachingMaterial $material): View
    {
        Gate::authorize('update', $material);

        return view('teacher.materials.edit', [
            'material' => $material,
            ...$this->formOptions($this->currentTeacher($request)),
        ]);
    }

    public function update(TeachingMaterialRequest $request, TeachingMaterial $material): RedirectResponse
    {
        Gate::authorize('update', $material);

        $data = $request->safe()->except('file');

        if ($request->hasFile('file')) {
            Storage::disk('local')->delete($material->file_path);

            $file = $request->file('file');
            $data['file_path'] = $file->store('materials', 'local');
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_size'] = $file->getSize();
        }

        $material->update($data);

        return redirect()->route('teacher.materials.show', $material)->with('success', __('Teaching material updated.'));
    }

    public function destroy(TeachingMaterial $material): RedirectResponse
    {
        Gate::authorize('delete', $material);

        $material->delete();

        return redirect()->route('teacher.materials.index')->with('success', __('Teaching material deleted.'));
    }

    public function download(TeachingMaterial $material): StreamedResponse
    {
        Gate::authorize('view', $material);

        abort_unless(Storage::disk('local')->exists($material->file_path), 404);

        return Storage::disk('local')->download($material->file_path, $material->file_name);
    }

    /**
     * Dropdown options limited to what this teacher teaches.
     *
     * @return array{subjectOptions: array<int, string>, classOptions: array<int, string>, typeOptions: array<string, string>}
     */
    private function formOptions(Teacher $teacher): array
    {
        return [
            'subjectOptions' => $teacher->subjectsTaught()->pluck('name', 'id')->all(),
            'classOptions' => $teacher->classesTaught()->pluck('name', 'id')->all(),
            'typeOptions' => collect(MaterialType::cases())->mapWithKeys(fn (MaterialType $type) => [$type->value => $type->label()])->all(),
        ];
    }
}
