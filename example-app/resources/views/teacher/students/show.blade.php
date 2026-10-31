<x-layouts.app :title="$student->name">
    <x-page-header :title="$student->name" :description="__('Student ID :code', ['code' => $student->student_code])" :back="route('teacher.students.index')" />

    <div class="grid gap-6 lg:grid-cols-3">
        <x-card title="Student Information">
            <dl class="space-y-4 text-[16px]">
                <div><dt class="text-sm text-slate-500">Name</dt><dd class="font-semibold">{{ $student->name }}</dd></div>
                <div><dt class="text-sm text-slate-500">Student ID</dt><dd>{{ $student->student_code }}</dd></div>
                <div><dt class="text-sm text-slate-500">Class</dt><dd><a href="{{ route('teacher.classes.show', $student->schoolClass) }}" class="link">{{ $student->schoolClass->name }}</a></dd></div>
                <div><dt class="text-sm text-slate-500">Email</dt><dd class="break-all">{{ $student->email ?: '—' }}</dd></div>
            </dl>
            <p class="mt-6 flex items-start gap-2 rounded-md bg-slate-50 p-3 text-sm text-slate-600">
                <x-icon name="info" class="mt-0.5 size-4" /> Student details can only be changed by the administrator.
            </p>
        </x-card>

        <div class="space-y-6 lg:col-span-2">
            <x-card title="Subject Performance" :padding="false">
                <x-table>
                    <thead><tr><th>Subject</th><th>Assignments due</th><th>On time</th><th>Late</th><th>Missing</th><th>Submission rate</th></tr></thead>
                    <tbody>
                        @forelse ($performance as $subject => $row)
                            <tr>
                                <td class="font-medium">{{ $subject }}</td>
                                <td>{{ $row['total'] }}</td>
                                <td>{{ $row['on_time'] }}</td>
                                <td>{{ $row['late'] }}</td>
                                <td>{{ $row['missing'] }}</td>
                                <td class="font-semibold">{{ $row['rate'] === null ? '—' : $row['rate'].'%' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-slate-500">No assignments yet for this student's class.</td></tr>
                        @endforelse
                    </tbody>
                </x-table>
            </x-card>

            <x-card title="Assignment Performance" :padding="false">
                <x-table>
                    <thead><tr><th>Assignment</th><th>Subject</th><th>Due date</th><th>Status</th><th>Submitted on</th></tr></thead>
                    <tbody>
                        @forelse ($assignments as $row)
                            <tr>
                                <td><a href="{{ route('teacher.assignments.show', $row['assignment']) }}" class="link">{{ $row['assignment']->title }}</a></td>
                                <td>{{ $row['assignment']->subject->name }}</td>
                                <td class="whitespace-nowrap">{{ $row['assignment']->due_at->translatedFormat('j M Y') }}</td>
                                <td>
                                    @if ($row['status'])
                                        <x-badge :color="$row['status']->color()">{{ $row['status']->label() }}</x-badge>
                                    @else
                                        <x-badge color="blue">Pending</x-badge>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap">{{ $row['submission']?->submitted_at->translatedFormat('j M Y') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-slate-500">No assignments yet.</td></tr>
                        @endforelse
                    </tbody>
                </x-table>
            </x-card>
        </div>
    </div>
</x-layouts.app>
