<x-layouts.app :title="$schoolClass->name">
    <x-page-header :title="'Class '.$schoolClass->name" :description="'Form '.$schoolClass->form_level.' · Academic year '.$schoolClass->academic_year.' · '.$students->count().' students'" :back="route('teacher.classes.index')">
        <x-slot:actions>
            <a href="{{ route('teacher.assignments.create', ['school_class_id' => $schoolClass->id]) }}" class="btn btn-secondary"><x-icon name="plus" /> New Assignment</a>
            <a href="{{ route('teacher.materials.create', ['school_class_id' => $schoolClass->id]) }}" class="btn btn-primary"><x-icon name="upload" /> Upload Material</a>
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-card title="Student List" :padding="false">
                <x-table>
                    <thead><tr><th class="w-12">#</th><th>Name</th><th>Student ID</th><th>Email</th><th></th></tr></thead>
                    <tbody>
                        @forelse ($students as $student)
                            <tr>
                                <td class="text-slate-500">{{ $loop->iteration }}</td>
                                <td class="font-medium">{{ $student->name }}</td>
                                <td>{{ $student->student_code }}</td>
                                <td class="break-all">{{ $student->email ?: '—' }}</td>
                                <td class="text-right"><a href="{{ route('teacher.students.show', $student) }}" class="link">View</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-slate-500">No students in this class yet.</td></tr>
                        @endforelse
                    </tbody>
                </x-table>
            </x-card>

            <x-card title="Recent Assignments" :padding="false">
                <x-table>
                    <thead><tr><th>Title</th><th>Subject</th><th>Due date</th><th>Submitted</th></tr></thead>
                    <tbody>
                        @forelse ($assignments as $assignment)
                            @php($summary = $assignment->submissionSummary())
                            <tr>
                                <td><a href="{{ route('teacher.assignments.show', $assignment) }}" class="link">{{ $assignment->title }}</a></td>
                                <td>{{ $assignment->subject->name }}</td>
                                <td class="whitespace-nowrap">{{ $assignment->due_at->format('j M Y') }}</td>
                                <td>{{ $summary['submitted'] }}/{{ $summary['students'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-slate-500">You have not set any assignments for this class.</td></tr>
                        @endforelse
                    </tbody>
                </x-table>
            </x-card>

            <x-card title="Teaching Materials" :padding="false">
                <x-table>
                    <thead><tr><th>Title</th><th>Subject</th><th>Type</th><th>Uploaded</th></tr></thead>
                    <tbody>
                        @forelse ($materials as $material)
                            <tr>
                                <td><a href="{{ route('teacher.materials.show', $material) }}" class="link">{{ $material->title }}</a></td>
                                <td>{{ $material->subject->name }}</td>
                                <td>{{ $material->type->label() }}</td>
                                <td class="whitespace-nowrap">{{ $material->created_at->format('j M Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-slate-500">No materials uploaded for this class.</td></tr>
                        @endforelse
                    </tbody>
                </x-table>
            </x-card>
        </div>

        <div class="space-y-6">
            <x-card title="Teachers">
                <ul class="space-y-3">
                    @foreach ($teachingLoads as $load)
                        <li>
                            <p class="font-semibold">{{ $load->subject->name }}</p>
                            <p class="text-[15px] text-slate-600">{{ $load->teacher->user->name }}</p>
                        </li>
                    @endforeach
                </ul>
            </x-card>

            <x-card title="Class Schedule">
                <ul class="divide-y divide-slate-100">
                    @forelse ($schedule as $entry)
                        <li class="py-2.5 first:pt-0 last:pb-0">
                            <p class="font-semibold">{{ $entry->day->label() }} · {{ $entry->timeRange() }}</p>
                            <p class="text-[15px] text-slate-600">{{ $entry->subject->name }} · {{ $entry->room }}</p>
                        </li>
                    @empty
                        <li class="text-slate-600">No timetable entries yet.</li>
                    @endforelse
                </ul>
            </x-card>
        </div>
    </div>
</x-layouts.app>
