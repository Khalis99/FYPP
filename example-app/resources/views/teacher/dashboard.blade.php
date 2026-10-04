<x-layouts.app title="Dashboard">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-900 sm:text-3xl">Welcome back, {{ auth()->user()->name }}!</h2>
        <p class="mt-1 text-[16px] text-slate-600">{{ now()->format('l, j F Y') }}</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card label="Total Classes" :value="$stats['classes']" icon="classes" color="blue" :href="route('teacher.classes.index')" />
        <x-stat-card label="Teaching Materials" :value="$stats['materials']" icon="materials" color="green" :href="route('teacher.materials.index')" />
        <x-stat-card label="Active Assignments" :value="$stats['activeAssignments']" icon="assignments" color="purple" :href="route('teacher.assignments.index', ['status' => 'active'])" />
        <x-stat-card label="Pending Submissions" :value="$stats['pendingSubmissions']" icon="clock" color="amber" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-card title="Upcoming Assignments" :padding="false">
                <x-slot:actions>
                    <a href="{{ route('teacher.assignments.index') }}" class="btn btn-secondary btn-sm">View all</a>
                </x-slot:actions>

                @if ($upcomingAssignments->isEmpty())
                    <x-empty-state icon="assignments" title="No upcoming assignments" message="Assignments you create will appear here until their due date.">
                        <a href="{{ route('teacher.assignments.create') }}" class="btn btn-primary"><x-icon name="plus" /> Create Assignment</a>
                    </x-empty-state>
                @else
                    <x-table>
                        <thead>
                            <tr><th>Assignment</th><th>Class</th><th>Due date</th><th>Submitted</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($upcomingAssignments as $assignment)
                                @php($summary = $assignment->submissionSummary())
                                <tr>
                                    <td><a href="{{ route('teacher.assignments.show', $assignment) }}" class="link">{{ $assignment->title }}</a></td>
                                    <td>{{ $assignment->schoolClass->name }} <span class="block text-sm text-slate-500">{{ $assignment->subject->name }}</span></td>
                                    <td class="whitespace-nowrap">{{ $assignment->due_at->format('j M Y') }}<span class="block text-sm text-slate-500">{{ $assignment->due_at->diffForHumans() }}</span></td>
                                    <td class="whitespace-nowrap">
                                        <span class="font-semibold">{{ $summary['submitted'] }}/{{ $summary['students'] }}</span>
                                        <span class="block text-sm text-slate-500">{{ $summary['rate'] }}%</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </x-table>
                @endif
            </x-card>

            <x-card title="Recent Announcements">
                <x-slot:actions>
                    <a href="{{ route('teacher.announcements.index') }}" class="btn btn-secondary btn-sm">View all</a>
                </x-slot:actions>

                <ul class="divide-y divide-slate-100">
                    @forelse ($announcements as $announcement)
                        <li class="py-3 first:pt-0 last:pb-0">
                            <a href="{{ route('teacher.announcements.show', $announcement) }}" class="group block">
                                <div class="flex items-start justify-between gap-3">
                                    <p class="font-semibold text-slate-900 group-hover:text-brand-700">{{ $announcement->title }}</p>
                                    @unless ($readAnnouncementIds->contains($announcement->id))
                                        <x-badge color="blue">New</x-badge>
                                    @endunless
                                </div>
                                <p class="mt-0.5 line-clamp-2 text-[15px] text-slate-600">{{ $announcement->body }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $announcement->published_at->format('j M Y') }}</p>
                            </a>
                        </li>
                    @empty
                        <li class="text-slate-600">There are no announcements right now.</li>
                    @endforelse
                </ul>
            </x-card>
        </div>

        <div class="space-y-6">
            <section class="rounded-lg bg-gradient-to-br from-brand-800 to-brand-500 p-6 text-white shadow-sm">
                <x-icon name="sparkles" class="size-10" />
                <h3 class="mt-3 text-xl font-bold">RPH Generator</h3>
                <p class="mt-1 text-[15px] text-brand-100">Prepare a draft RPH from your lesson details in a few minutes. You review and edit it before saving.</p>
                <a href="{{ route('teacher.rph.create') }}" class="btn mt-4 bg-white text-brand-800 hover:bg-brand-50">Generate RPH</a>
            </section>

            <x-card :title="'Today\'s Schedule'">
                @if (! $today)
                    <p class="text-slate-600">No classes today — enjoy your weekend!</p>
                @else
                    <ul class="divide-y divide-slate-100">
                        @forelse ($todaySchedule as $entry)
                            <li class="py-3 first:pt-0 last:pb-0">
                                <p class="font-semibold">{{ $entry->subject->name }} ({{ $entry->schoolClass->name }})</p>
                                <p class="text-[15px] text-slate-600">{{ $entry->timeRange() }}</p>
                                <p class="text-[15px] text-slate-500">{{ $entry->room }}</p>
                            </li>
                        @empty
                            <li class="text-slate-600">You have no classes scheduled today.</li>
                        @endforelse
                    </ul>
                @endif
                <a href="{{ route('teacher.timetable.index') }}" class="mt-4 inline-block text-[15px] link">View full timetable</a>
            </x-card>

            <x-card title="Quick Actions">
                <div class="grid gap-2">
                    <a href="{{ route('teacher.rph.create') }}" class="btn btn-secondary justify-start"><x-icon name="sparkles" class="text-brand-600" /> Generate RPH</a>
                    <a href="{{ route('teacher.materials.create') }}" class="btn btn-secondary justify-start"><x-icon name="upload" class="text-brand-600" /> Upload Material</a>
                    <a href="{{ route('teacher.assignments.create') }}" class="btn btn-secondary justify-start"><x-icon name="plus" class="text-brand-600" /> Create Assignment</a>
                    <a href="{{ route('teacher.classes.index') }}" class="btn btn-secondary justify-start"><x-icon name="classes" class="text-brand-600" /> View Classes</a>
                </div>
            </x-card>
        </div>
    </div>
</x-layouts.app>
