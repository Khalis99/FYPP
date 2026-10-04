<x-layouts.app :title="$schoolClass->name">
    <x-page-header :title="'Class '.$schoolClass->name" :description="'Form '.$schoolClass->form_level.' · Academic year '.$schoolClass->academic_year" :back="route('admin.classes.index')">
        <x-slot:actions>
            <a href="{{ route('admin.classes.edit', $schoolClass) }}" class="btn btn-secondary"><x-icon name="edit" /> Edit</a>
            <a href="{{ route('admin.timetable.create', ['school_class_id' => $schoolClass->id]) }}" class="btn btn-secondary"><x-icon name="timetable" /> Add Timetable Entry</a>
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-card :title="'Students ('.$students->count().')'" :padding="false">
                <x-slot:actions>
                    <a href="{{ route('admin.students.create', ['school_class_id' => $schoolClass->id]) }}" class="btn btn-secondary btn-sm"><x-icon name="plus" class="size-4" /> New Student</a>
                </x-slot:actions>
                <x-table>
                    <thead><tr><th>Name</th><th>Student ID</th><th>Status</th><th class="text-right">Action</th></tr></thead>
                    <tbody>
                        @forelse ($students as $student)
                            <tr>
                                <td class="font-medium"><a href="{{ route('admin.students.show', $student) }}" class="hover:underline">{{ $student->name }}</a></td>
                                <td>{{ $student->student_code }}</td>
                                <td><x-active-badge :active="$student->is_active" /></td>
                                <td class="text-right">
                                    <x-delete-button :action="route('admin.classes.students.destroy', [$schoolClass, $student])" label="Remove"
                                                     :message="'Remove '.$student->name.' from '.$schoolClass->name.'? The student record is kept.'" />
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-slate-500">No students in this class yet.</td></tr>
                        @endforelse
                    </tbody>
                </x-table>
            </x-card>

            <x-card title="Add Existing Students">
                @if ($availableStudents->isEmpty())
                    <p class="text-slate-600">All active students are already in this class.</p>
                @else
                    <form method="POST" action="{{ route('admin.classes.students.store', $schoolClass) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label for="student_ids" class="form-label">Select students</label>
                            <select id="student_ids" name="student_ids[]" multiple size="8" class="form-input">
                                @foreach ($availableStudents as $student)
                                    <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->student_code }}){{ $student->schoolClass ? ' — currently in '.$student->schoolClass->name : ' — no class' }}</option>
                                @endforeach
                            </select>
                            <p class="form-hint">Hold Ctrl (or Cmd on Mac) to select more than one student. Students already in another class will be moved.</p>
                            @error('student_ids')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                        <button type="submit" class="btn btn-primary"><x-icon name="plus" /> Add to Class</button>
                    </form>
                @endif
            </x-card>
        </div>

        <div class="space-y-6">
            <x-card title="Subjects & Teachers">
                <ul class="mb-5 divide-y divide-slate-100">
                    @forelse ($teachingLoads as $load)
                        <li class="flex items-start justify-between gap-3 py-2.5 first:pt-0">
                            <div>
                                <p class="font-semibold">{{ $load->subject->name }}</p>
                                <p class="text-[15px] text-slate-600">{{ $load->teacher->user->name }}</p>
                            </div>
                            <x-delete-button :action="route('admin.classes.teachers.destroy', [$schoolClass, $load])" label="Remove"
                                             message="Remove this teacher from the subject? Their existing assignments and materials are kept." />
                        </li>
                    @empty
                        <li class="pb-2 text-slate-600">No teachers assigned yet.</li>
                    @endforelse
                </ul>

                <form method="POST" action="{{ route('admin.classes.teachers.store', $schoolClass) }}" class="space-y-4 border-t border-slate-100 pt-4">
                    @csrf
                    <p class="font-semibold">Assign teacher</p>
                    <x-form.select name="subject_id" label="Subject" :options="$subjectOptions" placeholder="Choose a subject" required />
                    <x-form.select name="teacher_id" label="Teacher" :options="$teacherOptions" placeholder="Choose a teacher" required />
                    <p class="form-hint">If the subject already has a teacher, they will be replaced.</p>
                    <button type="submit" class="btn btn-primary w-full">Assign</button>
                </form>
            </x-card>
        </div>
    </div>
</x-layouts.app>
