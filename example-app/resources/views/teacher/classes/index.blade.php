<x-layouts.app title="My Classes">
    <x-page-header title="My Classes" description="The classes and subjects you are assigned to teach." />

    <form method="GET" class="card mb-6 flex flex-col gap-3 p-4 sm:flex-row sm:items-end">
        <div class="flex-1">
            <x-form.input name="search" label="Search class or subject" :value="$search" placeholder="e.g. 1 Amanah or Science" />
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary"><x-icon name="search" /> Search</button>
            @if ($search)
                <a href="{{ route('teacher.classes.index') }}" class="btn btn-secondary">Clear</a>
            @endif
        </div>
    </form>

    @if ($loads->isEmpty())
        <div class="card">
            <x-empty-state icon="classes" title="No classes found" message="{{ $search ? 'Try a different search.' : 'You have not been assigned to any class yet. Please contact the administrator.' }}" />
        </div>
    @else
        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($loads as $load)
                @php($sessions = $schedule->get($load->school_class_id.'-'.$load->subject_id, collect()))
                <article class="card flex flex-col p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-xl font-bold text-slate-900">{{ $load->schoolClass->name }}</h3>
                            <p class="text-[16px] font-medium text-brand-700">{{ $load->subject->name }}</p>
                        </div>
                        <span class="rounded-lg bg-teal-50 p-2.5 text-teal-700"><x-icon name="classes" class="size-6" /></span>
                    </div>

                    <dl class="mt-4 space-y-2 text-[15px]">
                        <div class="flex gap-2"><dt class="w-24 text-slate-500">Students</dt><dd class="font-semibold">{{ $load->schoolClass->students_count }}</dd></div>
                        <div class="flex gap-2"><dt class="w-24 shrink-0 text-slate-500">Schedule</dt>
                            <dd>
                                @forelse ($sessions as $session)
                                    <span class="block">{{ $session->day->label() }}, {{ \Illuminate\Support\Carbon::parse($session->start_time)->format('g:i A') }}</span>
                                @empty
                                    <span class="text-slate-500">Not scheduled</span>
                                @endforelse
                            </dd>
                        </div>
                    </dl>

                    <div class="mt-auto grid grid-cols-2 gap-2 pt-5">
                        <a href="{{ route('teacher.classes.show', $load->schoolClass) }}" class="btn btn-primary btn-sm col-span-2">View Class</a>
                        <a href="{{ route('teacher.students.index', ['school_class_id' => $load->school_class_id]) }}" class="btn btn-secondary btn-sm">Students</a>
                        <a href="{{ route('teacher.assignments.index', ['school_class_id' => $load->school_class_id]) }}" class="btn btn-secondary btn-sm">Assignments</a>
                        <a href="{{ route('teacher.materials.index', ['school_class_id' => $load->school_class_id, 'subject_id' => $load->subject_id]) }}" class="btn btn-secondary btn-sm col-span-2">Materials</a>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</x-layouts.app>
