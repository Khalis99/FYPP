<x-layouts.app title="Timetable">
    <x-page-header title="My Teaching Timetable" description="Your weekly classes. Contact the administrator if anything needs to change." />

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
        @foreach ($days as $day)
            <section @class(['card overflow-hidden', 'ring-2 ring-brand-500' => $today === $day])>
                <h3 @class(['flex items-center justify-between px-4 py-3 text-lg font-bold', 'bg-brand-600 text-white' => $today === $day, 'bg-slate-50 text-slate-800 border-b border-slate-200' => $today !== $day])>
                    {{ $day->label() }}
                    @if ($today === $day)<span class="text-sm font-semibold">Today</span>@endif
                </h3>
                <ul class="divide-y divide-slate-100">
                    @forelse ($entries->get($day->value, collect()) as $entry)
                        <li class="px-4 py-3">
                            <p class="text-sm font-semibold text-brand-700">{{ $entry->timeRange() }}</p>
                            <p class="mt-0.5 text-[16px] font-semibold text-slate-900">{{ $entry->subject->name }}</p>
                            <p class="text-[15px] text-slate-700">{{ $entry->schoolClass->name }}</p>
                            <p class="text-[15px] text-slate-500">{{ $entry->room ?: '—' }}</p>
                        </li>
                    @empty
                        <li class="px-4 py-6 text-center text-[15px] text-slate-500">No classes</li>
                    @endforelse
                </ul>
            </section>
        @endforeach
    </div>
</x-layouts.app>
