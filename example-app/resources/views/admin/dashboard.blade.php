<x-layouts.app title="Admin Dashboard">
    <div class="mb-6">
        <h2 class="text-2xl font-extrabold text-slate-900 sm:text-3xl"><x-greeting :user="auth()->user()" /></h2>
        <p class="mt-1 text-[16px] text-slate-600">{{ config('schoolhub.school_name') }} · {{ now()->translatedFormat('l, j F Y') }}</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-5">
        <x-stat-card label="Teachers" :value="$stats['teachers']" icon="teachers" color="blue" :href="route('admin.teachers.index')" />
        <x-stat-card label="Students" :value="$stats['students']" icon="students" color="green" :href="route('admin.students.index')" />
        <x-stat-card label="Classes" :value="$stats['classes']" icon="classes" color="purple" :href="route('admin.classes.index')" />
        <x-stat-card label="Assignments" :value="$stats['assignments']" icon="assignments" color="amber" />
        <x-stat-card label="Announcements" :value="$stats['announcements']" icon="announcements" color="red" :href="route('admin.announcements.index')" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <x-card title="Class Statistics" :padding="false" class="lg:col-span-2">
            <x-slot:actions>
                <a href="{{ route('admin.classes.create') }}" class="btn btn-secondary btn-sm"><x-icon name="plus" class="size-4" /> New Class</a>
            </x-slot:actions>
            <x-table>
                <thead><tr><th>Class</th><th>Academic year</th><th>Students</th><th>Subjects assigned</th><th>Assignments</th></tr></thead>
                <tbody>
                    @forelse ($classes as $class)
                        <tr>
                            <td><a href="{{ route('admin.classes.show', $class) }}" class="link">{{ $class->name }}</a></td>
                            <td>{{ $class->academic_year }}</td>
                            <td>{{ $class->students_count }}</td>
                            <td>{{ $class->teaching_loads_count }}</td>
                            <td>{{ $class->assignments_count }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-slate-500">No classes yet.</td></tr>
                    @endforelse
                </tbody>
            </x-table>
        </x-card>

        <div class="space-y-6">
            <x-card title="Recent Users">
                <ul class="space-y-3">
                    @foreach ($recentUsers as $user)
                        <li class="flex items-center gap-3">
                            <x-avatar :user="$user" class="size-9 text-sm" />
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-semibold">{{ $user->name }}</p>
                                <p class="text-sm text-slate-500">{{ $user->role->label() }} · {{ $user->created_at->translatedFormat('j M Y') }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
                <a href="{{ route('admin.users.index') }}" class="mt-4 inline-block text-[15px] link">Manage users</a>
            </x-card>

            <x-card title="Recent Announcements">
                <ul class="divide-y divide-slate-100">
                    @forelse ($recentAnnouncements as $announcement)
                        <li class="py-2.5 first:pt-0 last:pb-0">
                            <a href="{{ route('admin.announcements.show', $announcement) }}" class="font-semibold hover:text-brand-700">{{ $announcement->title }}</a>
                            <p class="text-sm text-slate-500">
                                {{ $announcement->status->label() }} · {{ ($announcement->published_at ?? $announcement->created_at)->translatedFormat('j M Y') }}
                            </p>
                        </li>
                    @empty
                        <li class="text-slate-600">No announcements yet.</li>
                    @endforelse
                </ul>
                <a href="{{ route('admin.announcements.create') }}" class="btn btn-primary btn-sm mt-4"><x-icon name="plus" class="size-4" /> New Announcement</a>
            </x-card>
        </div>
    </div>
</x-layouts.app>
