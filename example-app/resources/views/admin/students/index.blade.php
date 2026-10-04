<x-layouts.app title="Students">
    <x-page-header title="Students" description="Student records for the whole school.">
        <x-slot:actions>
            <a href="{{ route('admin.students.create') }}" class="btn btn-primary"><x-icon name="plus" /> New Student</a>
        </x-slot:actions>
    </x-page-header>

    <form method="GET" class="card mb-6 grid gap-4 p-4 sm:grid-cols-2 lg:grid-cols-4 lg:items-end">
        <x-form.input name="search" label="Search name, ID or email" :value="$filters['search'] ?? ''" />
        <x-form.select name="school_class_id" label="Class" :options="$classOptions" :value="$filters['school_class_id'] ?? ''" placeholder="All classes" />
        <x-form.select name="status" label="Status" :options="['active' => 'Active', 'inactive' => 'Inactive']" :value="$filters['status'] ?? ''" placeholder="All" />
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary"><x-icon name="search" /> Search</button>
            @if (array_filter($filters))
                <a href="{{ route('admin.students.index') }}" class="btn btn-secondary">Clear</a>
            @endif
        </div>
    </form>

    <x-card :padding="false">
        <x-table>
            <thead><tr><th>Name</th><th>Student ID</th><th>Class</th><th>Email</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
            <tbody>
                @forelse ($students as $student)
                    <tr>
                        <td class="font-medium">{{ $student->name }}</td>
                        <td>{{ $student->student_code }}</td>
                        <td>{{ $student->schoolClass?->name ?? '—' }}</td>
                        <td class="break-all">{{ $student->email ?: '—' }}</td>
                        <td><x-active-badge :active="$student->is_active" /></td>
                        <td class="text-right whitespace-nowrap">
                            <div class="inline-flex gap-3">
                                <a href="{{ route('admin.students.show', $student) }}" class="link">View</a>
                                <a href="{{ route('admin.students.edit', $student) }}" class="link">Edit</a>
                                <x-delete-button :action="route('admin.students.destroy', $student)" message="Delete this student record and all of their submissions?" />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-slate-500">No students found.</td></tr>
                @endforelse
            </tbody>
        </x-table>
        <div class="border-t border-slate-100 p-4">{{ $students->links() }}</div>
    </x-card>
</x-layouts.app>
