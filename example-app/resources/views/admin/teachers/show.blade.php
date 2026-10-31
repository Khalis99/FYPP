<x-layouts.app :title="$teacher->user->name">
    <x-page-header :title="$teacher->user->name" :description="__('Teacher ID :code', ['code' => $teacher->teacher_code])" :back="route('admin.teachers.index')">
        <x-slot:actions>
            <a href="{{ route('admin.teachers.edit', $teacher) }}" class="btn btn-primary"><x-icon name="edit" /> Edit</a>
            <a href="{{ route('admin.timetable.create', ['teacher_id' => $teacher->id]) }}" class="btn btn-secondary"><x-icon name="timetable" /> Add Timetable Entry</a>
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-card title="Profile">
            <div class="mb-5 flex items-center gap-4">
                <x-avatar :user="$teacher->user" class="size-16 text-xl" />
                <x-active-badge :active="$teacher->user->is_active" />
            </div>
            <dl class="space-y-3 text-[16px]">
                <div><dt class="text-sm text-slate-500">Email</dt><dd class="break-all">{{ $teacher->user->email }}</dd></div>
                <div><dt class="text-sm text-slate-500">Phone</dt><dd>{{ $teacher->user->phone ?: '—' }}</dd></div>
                <div><dt class="text-sm text-slate-500">Department</dt><dd>{{ $teacher->department ?: '—' }}</dd></div>
            </dl>
            <div class="mt-5 grid grid-cols-3 gap-2 text-center">
                <div class="rounded-md bg-slate-50 p-2"><p class="text-xl font-bold">{{ $counts['materials'] }}</p><p class="text-xs text-slate-500">Materials</p></div>
                <div class="rounded-md bg-slate-50 p-2"><p class="text-xl font-bold">{{ $counts['assignments'] }}</p><p class="text-xs text-slate-500">Assignments</p></div>
                <div class="rounded-md bg-slate-50 p-2"><p class="text-xl font-bold">{{ $counts['rph'] }}</p><p class="text-xs text-slate-500">RPH</p></div>
            </div>
            <div class="mt-5"><x-status-toggle :user="$teacher->user" /></div>
        </x-card>

        <div class="space-y-6 lg:col-span-2">
            <x-card title="Classes & Subjects" :padding="false">
                <x-table>
                    <thead><tr><th>Class</th><th>Subject</th></tr></thead>
                    <tbody>
                        @forelse ($teachingLoads as $load)
                            <tr>
                                <td><a href="{{ route('admin.classes.show', $load->schoolClass) }}" class="link">{{ $load->schoolClass->name }}</a></td>
                                <td>{{ $load->subject->name }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-center text-slate-500">Not assigned to any class yet. Assign from a class page.</td></tr>
                        @endforelse
                    </tbody>
                </x-table>
            </x-card>

            <x-card title="Timetable" :padding="false">
                <x-table>
                    <thead><tr><th>Day</th><th>Time</th><th>Subject</th><th>Class</th><th>Room</th></tr></thead>
                    <tbody>
                        @forelse ($timetables as $entry)
                            <tr>
                                <td>{{ $entry->day->label() }}</td>
                                <td class="whitespace-nowrap">{{ $entry->timeRange() }}</td>
                                <td>{{ $entry->subject->name }}</td>
                                <td>{{ $entry->schoolClass->name }}</td>
                                <td>{{ $entry->room ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-slate-500">No timetable entries.</td></tr>
                        @endforelse
                    </tbody>
                </x-table>
            </x-card>
        </div>
    </div>
</x-layouts.app>
