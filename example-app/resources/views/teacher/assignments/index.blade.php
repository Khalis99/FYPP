<x-layouts.app title="Assignments">
    <x-page-header title="Assignments" description="Create assignments and monitor student submissions.">
        <x-slot:actions>
            <a href="{{ route('teacher.assignments.create') }}" class="btn btn-primary"><x-icon name="plus" /> Create Assignment</a>
        </x-slot:actions>
    </x-page-header>

    <form method="GET" class="card mb-6 grid gap-4 p-4 sm:grid-cols-2 lg:grid-cols-4 lg:items-end">
        <div class="sm:col-span-2 lg:col-span-1">
            <x-form.input name="search" label="Search title" :value="$filters['search'] ?? ''" />
        </div>
        <x-form.select name="school_class_id" label="Class" :options="$classOptions" :value="$filters['school_class_id'] ?? ''" placeholder="All classes" />
        <x-form.select name="status" label="Status" :options="['active' => 'Active (not yet due)', 'closed' => 'Closed (past due)']" :value="$filters['status'] ?? ''" placeholder="All" />
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary"><x-icon name="search" /> Search</button>
            @if (array_filter($filters))
                <a href="{{ route('teacher.assignments.index') }}" class="btn btn-secondary">Clear</a>
            @endif
        </div>
    </form>

    <x-card :padding="false">
        @if ($assignments->isEmpty())
            <x-empty-state icon="assignments" title="No assignments found" message="{{ array_filter($filters) ? 'Try changing your search or filters.' : 'Create your first assignment for one of your classes.' }}" />
        @else
            <x-table>
                <thead>
                    <tr><th>Assignment</th><th>Class</th><th>Due date</th><th>Status</th><th class="min-w-44">Submissions</th><th class="text-right">Actions</th></tr>
                </thead>
                <tbody>
                    @foreach ($assignments as $assignment)
                        @php($summary = $assignment->submissionSummary())
                        <tr>
                            <td><a href="{{ route('teacher.assignments.show', $assignment) }}" class="link">{{ $assignment->title }}</a></td>
                            <td>{{ $assignment->schoolClass->name }}<span class="block text-sm text-slate-500">{{ $assignment->subject->name }}</span></td>
                            <td class="whitespace-nowrap">{{ $assignment->due_at->format('j M Y, g:i A') }}</td>
                            <td>
                                @if ($assignment->isActive())
                                    <x-badge color="green">Active</x-badge>
                                @else
                                    <x-badge>Closed</x-badge>
                                @endif
                            </td>
                            <td><x-submission-progress :submitted="$summary['submitted']" :students="$summary['students']" /></td>
                            <td class="text-right whitespace-nowrap">
                                <div class="inline-flex gap-3">
                                    <a href="{{ route('teacher.assignments.show', $assignment) }}" class="link">Submissions</a>
                                    <a href="{{ route('teacher.assignments.edit', $assignment) }}" class="link">Edit</a>
                                    <x-delete-button :action="route('teacher.assignments.destroy', $assignment)" message="Delete this assignment and all its recorded submissions?" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </x-table>
            <div class="border-t border-slate-100 p-4">{{ $assignments->links() }}</div>
        @endif
    </x-card>
</x-layouts.app>
