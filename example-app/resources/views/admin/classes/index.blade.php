<x-layouts.app title="Classes">
    <x-page-header title="Classes" description="Create classes, assign teachers and manage class lists.">
        <x-slot:actions>
            <a href="{{ route('admin.subjects.index') }}" class="btn btn-secondary"><x-icon name="book" /> Manage Subjects</a>
            <a href="{{ route('admin.classes.create') }}" class="btn btn-primary"><x-icon name="plus" /> New Class</a>
        </x-slot:actions>
    </x-page-header>

    <form method="GET" class="card mb-6 grid gap-4 p-4 sm:grid-cols-3 sm:items-end">
        <x-form.input name="search" label="Search class name" :value="$filters['search'] ?? ''" />
        <x-form.select name="academic_year" label="Academic year" :options="$yearOptions" :value="$filters['academic_year'] ?? ''" placeholder="All years" />
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary"><x-icon name="search" /> Search</button>
            @if (array_filter($filters))
                <a href="{{ route('admin.classes.index') }}" class="btn btn-secondary">Clear</a>
            @endif
        </div>
    </form>

    <x-card :padding="false">
        <x-table>
            <thead><tr><th>Class</th><th>Form</th><th>Academic year</th><th>Students</th><th>Subjects & teachers</th><th class="text-right">Actions</th></tr></thead>
            <tbody>
                @forelse ($classes as $class)
                    <tr>
                        <td class="font-semibold"><a href="{{ route('admin.classes.show', $class) }}" class="link">{{ $class->name }}</a></td>
                        <td>{{ $class->form_level }}</td>
                        <td>{{ $class->academic_year }}</td>
                        <td>{{ $class->students_count }}</td>
                        <td class="text-[14px]">
                            @forelse ($class->teachingLoads as $load)
                                <span class="block">{{ $load->subject->name }} — <span class="text-slate-600">{{ $load->teacher->user->name }}</span></span>
                            @empty
                                <span class="text-slate-500">No teachers assigned</span>
                            @endforelse
                        </td>
                        <td class="text-right whitespace-nowrap">
                            <div class="inline-flex gap-3">
                                <a href="{{ route('admin.classes.show', $class) }}" class="link">Manage</a>
                                <a href="{{ route('admin.classes.edit', $class) }}" class="link">Edit</a>
                                <x-delete-button :action="route('admin.classes.destroy', $class)" message="Delete this class? Its assignments, materials links and timetable entries will be removed, and its students will have no class." />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-slate-500">No classes found.</td></tr>
                @endforelse
            </tbody>
        </x-table>
        <div class="border-t border-slate-100 p-4">{{ $classes->links() }}</div>
    </x-card>
</x-layouts.app>
