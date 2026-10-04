<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Weekday;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TimetableRequest;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Timetable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TimetableController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['teacher_id', 'school_class_id', 'day']);

        $entries = Timetable::query()
            ->with(['teacher.user', 'subject', 'schoolClass'])
            ->when($filters['teacher_id'] ?? null, fn (Builder $q, $id) => $q->where('teacher_id', $id))
            ->when($filters['school_class_id'] ?? null, fn (Builder $q, $id) => $q->where('school_class_id', $id))
            ->when($filters['day'] ?? null, fn (Builder $q, $day) => $q->where('day', $day))
            ->orderBy('day')
            ->orderBy('start_time')
            ->get()
            ->groupBy(fn (Timetable $entry) => $entry->day->value);

        return view('admin.timetable.index', [
            'entries' => $entries,
            'filters' => $filters,
            'days' => Weekday::cases(),
            ...$this->formOptions(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.timetable.create', [
            'timetable' => new Timetable([
                'day' => Weekday::tryFrom($request->integer('day')) ?? Weekday::Monday,
                'teacher_id' => $request->integer('teacher_id') ?: null,
                'school_class_id' => $request->integer('school_class_id') ?: null,
            ]),
            ...$this->formOptions(),
        ]);
    }

    public function store(TimetableRequest $request): RedirectResponse
    {
        Timetable::create($request->validated());

        return redirect()->route('admin.timetable.index', ['day' => $request->validated('day')])->with('success', 'Timetable entry added.');
    }

    public function edit(Timetable $timetable): View
    {
        return view('admin.timetable.edit', ['timetable' => $timetable, ...$this->formOptions()]);
    }

    public function update(TimetableRequest $request, Timetable $timetable): RedirectResponse
    {
        $timetable->update($request->validated());

        return redirect()->route('admin.timetable.index')->with('success', 'Timetable entry updated.');
    }

    public function destroy(Timetable $timetable): RedirectResponse
    {
        $timetable->delete();

        return back()->with('success', 'Timetable entry deleted.');
    }

    /**
     * @return array{dayOptions: array<int, string>, teacherOptions: array<int, string>, classOptions: array<int, string>, subjectOptions: array<int, string>}
     */
    private function formOptions(): array
    {
        return [
            'dayOptions' => collect(Weekday::cases())->mapWithKeys(fn (Weekday $day) => [$day->value => $day->label()])->all(),
            'teacherOptions' => Teacher::with('user')->get()->sortBy('user.name')->mapWithKeys(fn (Teacher $t) => [$t->id => $t->user->name])->all(),
            'classOptions' => SchoolClass::orderBy('form_level')->orderBy('name')->pluck('name', 'id')->all(),
            'subjectOptions' => Subject::orderBy('name')->pluck('name', 'id')->all(),
        ];
    }
}
