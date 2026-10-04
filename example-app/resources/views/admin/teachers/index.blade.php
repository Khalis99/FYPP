<x-layouts.app title="Teachers">
    <x-page-header title="Teachers" description="Teacher accounts and profiles.">
        <x-slot:actions>
            <a href="{{ route('admin.teachers.create') }}" class="btn btn-primary"><x-icon name="plus" /> New Teacher</a>
        </x-slot:actions>
    </x-page-header>

    <form method="GET" class="card mb-6 grid gap-4 p-4 sm:grid-cols-2 lg:grid-cols-4 lg:items-end">
        <x-form.input name="search" label="Search name, email or ID" :value="$filters['search'] ?? ''" />
        <x-form.select name="department" label="Department" :options="$departmentOptions" :value="$filters['department'] ?? ''" placeholder="All departments" />
        <x-form.select name="status" label="Status" :options="['active' => 'Active', 'inactive' => 'Inactive']" :value="$filters['status'] ?? ''" placeholder="All" />
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary"><x-icon name="search" /> Search</button>
            @if (array_filter($filters))
                <a href="{{ route('admin.teachers.index') }}" class="btn btn-secondary">Clear</a>
            @endif
        </div>
    </form>

    <x-card :padding="false">
        <x-table>
            <thead><tr><th>Name</th><th>Teacher ID</th><th>Email</th><th>Department</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
            <tbody>
                @forelse ($teachers as $teacher)
                    <tr>
                        <td class="font-medium"><span class="flex items-center gap-3"><x-avatar :user="$teacher->user" class="size-8 text-xs" /> {{ $teacher->user->name }}</span></td>
                        <td>{{ $teacher->teacher_code }}</td>
                        <td class="break-all">{{ $teacher->user->email }}</td>
                        <td>{{ $teacher->department ?: '—' }}</td>
                        <td><x-active-badge :active="$teacher->user->is_active" /></td>
                        <td class="text-right whitespace-nowrap">
                            <div class="inline-flex gap-3">
                                <a href="{{ route('admin.teachers.show', $teacher) }}" class="link">View</a>
                                <a href="{{ route('admin.teachers.edit', $teacher) }}" class="link">Edit</a>
                                <x-status-toggle :user="$teacher->user" />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-slate-500">No teachers found.</td></tr>
                @endforelse
            </tbody>
        </x-table>
        <div class="border-t border-slate-100 p-4">{{ $teachers->links() }}</div>
    </x-card>
</x-layouts.app>
