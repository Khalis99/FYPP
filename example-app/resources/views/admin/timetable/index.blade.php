<x-layouts.app title="Timetable">
    <x-page-header title="Timetable" description="Weekly teaching schedule for all classes.">
        <x-slot:actions>
            <a href="{{ route('admin.timetable.create') }}" class="btn btn-primary"><x-icon name="plus" /> New Entry</a>
        </x-slot:actions>
    </x-page-header>

    <form method="GET" class="card mb-6 grid gap-4 p-4 sm:grid-cols-2 lg:grid-cols-4 lg:items-end">
        <x-form.select name="teacher_id" label="Teacher" :options="$teacherOptions" :value="$filters['teacher_id'] ?? ''" placeholder="All teachers" />
        <x-form.select name="school_class_id" label="Class" :options="$classOptions" :value="$filters['school_class_id'] ?? ''" placeholder="All classes" />
        <x-form.select name="day" label="Day" :options="$dayOptions" :value="$filters['day'] ?? ''" placeholder="All days" />
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary"><x-icon name="search" /> Filter</button>
            @if (array_filter($filters))
                <a href="{{ route('admin.timetable.index') }}" class="btn btn-secondary">Clear</a>
            @endif
        </div>
    </form>

    <div class="space-y-6">
        @foreach ($days as $day)
            @continue(($filters['day'] ?? null) && (int) $filters['day'] !== $day->value)
            <x-card :title="$day->label()" :padding="false">
                <x-slot:actions>
                    <a href="{{ route('admin.timetable.create', ['day' => $day->value]) }}" class="btn btn-secondary btn-sm"><x-icon name="plus" class="size-4" /> Add</a>
                </x-slot:actions>
                <x-table>
                    <thead><tr><th>Time</th><th>Subject</th><th>Class</th><th>Teacher</th><th>Room</th><th class="text-right">Actions</th></tr></thead>
                    <tbody>
                        @forelse ($entries->get($day->value, collect()) as $entry)
                            <tr>
                                <td class="whitespace-nowrap font-medium">{{ $entry->timeRange() }}</td>
                                <td>{{ $entry->subject->name }}</td>
                                <td>{{ $entry->schoolClass->name }}</td>
                                <td>{{ $entry->teacher->user->name }}</td>
                                <td>{{ $entry->room ?: '—' }}</td>
                                <td class="text-right whitespace-nowrap">
                                    <div class="inline-flex gap-3">
                                        <a href="{{ route('admin.timetable.edit', $entry) }}" class="link">Edit</a>
                                        <x-delete-button :action="route('admin.timetable.destroy', $entry)" message="Delete this timetable entry?" />
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-slate-500">No entries.</td></tr>
                        @endforelse
                    </tbody>
                </x-table>
            </x-card>
        @endforeach
    </div>
</x-layouts.app>
