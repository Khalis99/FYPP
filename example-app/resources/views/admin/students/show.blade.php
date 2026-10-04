<x-layouts.app :title="$student->name">
    <x-page-header :title="$student->name" :description="'Student ID '.$student->student_code" :back="route('admin.students.index')">
        <x-slot:actions>
            <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-primary"><x-icon name="edit" /> Edit</a>
            <x-delete-button :action="route('admin.students.destroy', $student)" button="btn btn-danger" message="Delete this student record and all of their submissions?" />
        </x-slot:actions>
    </x-page-header>

    <x-card class="max-w-3xl">
        <dl class="grid gap-x-6 gap-y-5 sm:grid-cols-2 text-[16px]">
            <div><dt class="text-sm text-slate-500">Name</dt><dd class="font-semibold">{{ $student->name }}</dd></div>
            <div><dt class="text-sm text-slate-500">Student ID</dt><dd>{{ $student->student_code }}</dd></div>
            <div><dt class="text-sm text-slate-500">Class</dt><dd>
                @if ($student->schoolClass)
                    <a href="{{ route('admin.classes.show', $student->schoolClass) }}" class="link">{{ $student->schoolClass->name }}</a>
                @else — @endif
            </dd></div>
            <div><dt class="text-sm text-slate-500">Email</dt><dd class="break-all">{{ $student->email ?: '—' }}</dd></div>
            <div><dt class="text-sm text-slate-500">Status</dt><dd><x-active-badge :active="$student->is_active" /></dd></div>
            <div><dt class="text-sm text-slate-500">Submissions recorded</dt><dd>{{ $submissionCount }}</dd></div>
        </dl>
    </x-card>
</x-layouts.app>
