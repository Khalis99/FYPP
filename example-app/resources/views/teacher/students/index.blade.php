<x-layouts.app title="Student Records">
    <x-page-header title="Student Records" description="Students in the classes you teach." />

    <form method="GET" class="card mb-6 grid gap-4 p-4 sm:grid-cols-3 sm:items-end">
        <x-form.input name="search" label="Search name or student ID" :value="$filters['search'] ?? ''" />
        <x-form.select name="school_class_id" label="Class" :options="$classOptions" :value="$filters['school_class_id'] ?? ''" placeholder="All my classes" />
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary"><x-icon name="search" /> Search</button>
            @if (array_filter($filters))
                <a href="{{ route('teacher.students.index') }}" class="btn btn-secondary">Clear</a>
            @endif
        </div>
    </form>

    <x-card :padding="false">
        @if ($students->isEmpty())
            <x-empty-state icon="students" title="No students found" message="Try a different name, student ID or class." />
        @else
            <x-table>
                <thead><tr><th>Name</th><th>Student ID</th><th>Class</th><th>Email</th><th class="text-right">Action</th></tr></thead>
                <tbody>
                    @foreach ($students as $student)
                        <tr>
                            <td class="font-medium">{{ $student->name }}</td>
                            <td>{{ $student->student_code }}</td>
                            <td>{{ $student->schoolClass?->name }}</td>
                            <td class="break-all">{{ $student->email ?: '—' }}</td>
                            <td class="text-right"><a href="{{ route('teacher.students.show', $student) }}" class="link">View profile</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </x-table>
            <div class="border-t border-slate-100 p-4">{{ $students->links() }}</div>
        @endif
    </x-card>
</x-layouts.app>
