<x-layouts.app :title="$assignment->title">
    <x-page-header :title="$assignment->title" :description="$assignment->subject->name.' · '.$assignment->schoolClass->name" :back="route('teacher.assignments.index')">
        <x-slot:actions>
            <a href="{{ route('teacher.assignments.edit', $assignment) }}" class="btn btn-secondary"><x-icon name="edit" /> Edit</a>
            <x-delete-button :action="route('teacher.assignments.destroy', $assignment)" button="btn btn-danger" message="Delete this assignment and all its recorded submissions?" />
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card label="Students" :value="$summary['students']" icon="students" color="blue" />
        <x-stat-card label="Submitted" :value="$summary['submitted']" icon="check" color="green" />
        <x-stat-card label="Not submitted" :value="$summary['not_submitted']" icon="warning" color="red" />
        <x-stat-card label="Submitted late" :value="$summary['late']" icon="clock" color="amber" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-card title="Student Submissions" :padding="false">
                <x-slot:actions>
                    <div class="flex flex-wrap gap-1.5 text-sm">
                        <a href="{{ route('teacher.assignments.show', $assignment) }}" @class(['rounded-full px-3 py-1 font-semibold', 'bg-brand-600 text-white' => ! $statusFilter, 'bg-slate-100 text-slate-700' => $statusFilter])>All</a>
                        @foreach (\App\Enums\SubmissionStatus::cases() as $status)
                            <a href="{{ route('teacher.assignments.show', [$assignment, 'status' => $status->value]) }}" @class(['rounded-full px-3 py-1 font-semibold', 'bg-brand-600 text-white' => $statusFilter === $status, 'bg-slate-100 text-slate-700' => $statusFilter !== $status])>{{ $status->label() }}</a>
                        @endforeach
                    </div>
                </x-slot:actions>

                <div class="px-5 pt-4">
                    <x-submission-progress :submitted="$summary['submitted']" :students="$summary['students']" />
                </div>

                <x-table class="mt-3">
                    <thead><tr><th>Student name</th><th>Student ID</th><th>Status</th><th>Submission date</th><th>File</th><th class="text-right">Action</th></tr></thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr>
                                <td class="font-medium"><a href="{{ route('teacher.students.show', $row['student']) }}" class="hover:underline">{{ $row['student']->name }}</a></td>
                                <td>{{ $row['student']->student_code }}</td>
                                <td><x-badge :color="$row['status']->color()">{{ $row['status']->label() }}</x-badge></td>
                                <td class="whitespace-nowrap">{{ $row['submission']?->submitted_at->translatedFormat('j M Y, g:i A') ?? '—' }}</td>
                                <td class="whitespace-nowrap">
                                    @if ($row['submission']?->file_path)
                                        <a href="{{ route('teacher.submissions.download', $row['submission']) }}" class="link inline-flex items-center gap-1"><x-icon name="download" class="size-4" /> Download</a>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="text-right whitespace-nowrap">
                                    @if ($row['submission'])
                                        <x-delete-button :action="route('teacher.submissions.destroy', $row['submission'])" label="Remove" message="Remove this recorded submission? The student will be marked as not submitted." />
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-slate-500">No students match this filter.</td></tr>
                        @endforelse
                    </tbody>
                </x-table>
            </x-card>
        </div>

        <div class="space-y-6">
            <x-card title="Assignment Details">
                <dl class="space-y-4 text-[15px]">
                    <div>
                        <dt class="text-sm text-slate-500">Due date</dt>
                        <dd class="font-semibold">{{ $assignment->due_at->translatedFormat('l, j F Y, g:i A') }}</dd>
                        <dd>@if ($assignment->isActive())<x-badge color="green">Active</x-badge>@else<x-badge>Closed</x-badge>@endif</dd>
                    </div>
                    @if ($assignment->description)
                        <div><dt class="text-sm text-slate-500">Description</dt><dd class="whitespace-pre-line">{{ $assignment->description }}</dd></div>
                    @endif
                    @if ($assignment->instructions)
                        <div><dt class="text-sm text-slate-500">Instructions</dt><dd class="whitespace-pre-line">{{ $assignment->instructions }}</dd></div>
                    @endif
                    @if ($assignment->attachment_path)
                        <div>
                            <dt class="text-sm text-slate-500">Attachment</dt>
                            <dd><a href="{{ route('teacher.assignments.attachment', $assignment) }}" class="link inline-flex items-center gap-1 break-all"><x-icon name="paperclip" class="size-4" /> {{ $assignment->attachment_name }}</a></dd>
                        </div>
                    @endif
                </dl>
            </x-card>

            <x-card title="Record a Submission">
                @if ($pendingStudents->isEmpty())
                    <p class="text-slate-600">All students have submitted.</p>
                @else
                    <p class="mb-4 text-sm text-slate-600">Use this when a student hands in work on paper or by email.</p>
                    <form method="POST" action="{{ route('teacher.submissions.store', $assignment) }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <x-form.select name="student_id" label="Student" :options="$pendingStudents->pluck('name', 'id')->all()" placeholder="Choose a student" required />
                        <x-form.input name="submitted_at" type="datetime-local" label="Date received" :value="now()->format('Y-m-d\TH:i')" required />
                        <x-form.file name="file" label="Student's work (optional)" />
                        <x-form.textarea name="remarks" label="Remarks (optional)" rows="2" />
                        <button type="submit" class="btn btn-primary w-full" data-loading-text="Saving…">Record Submission</button>
                    </form>
                @endif
            </x-card>
        </div>
    </div>
</x-layouts.app>
