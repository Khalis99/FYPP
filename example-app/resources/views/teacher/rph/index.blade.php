<x-layouts.app title="My RPH">
    <x-page-header title="My RPH" description="Your saved daily lesson plans.">
        <x-slot:actions>
            <a href="{{ route('teacher.rph.create') }}" class="btn btn-primary"><x-icon name="sparkles" /> Generate New RPH</a>
        </x-slot:actions>
    </x-page-header>

    <form method="GET" class="card mb-6 grid gap-4 p-4 sm:grid-cols-2 lg:grid-cols-5 lg:items-end">
        <div class="lg:col-span-2">
            <x-form.input name="search" label="Search title or topic" :value="$filters['search'] ?? ''" />
        </div>
        <x-form.select name="subject_id" label="Subject" :options="$subjectOptions" :value="$filters['subject_id'] ?? ''" placeholder="All subjects" />
        <x-form.select name="school_class_id" label="Class" :options="$classOptions" :value="$filters['school_class_id'] ?? ''" placeholder="All classes" />
        <x-form.input name="date" type="date" label="Lesson date" :value="$filters['date'] ?? ''" />
        <div class="flex gap-2 sm:col-span-2 lg:col-span-5">
            <button type="submit" class="btn btn-primary"><x-icon name="search" /> Search</button>
            @if (array_filter($filters))
                <a href="{{ route('teacher.rph.index') }}" class="btn btn-secondary">Clear filters</a>
            @endif
        </div>
    </form>

    <x-card :padding="false">
        @if ($documents->isEmpty())
            <x-empty-state icon="document" title="No RPH found" message="{{ array_filter($filters) ? __('Try changing your search or filters.') : __('Use the RPH Generator to prepare your first lesson plan.') }}">
                @unless (array_filter($filters))
                    <a href="{{ route('teacher.rph.create') }}" class="btn btn-primary"><x-icon name="sparkles" /> Open RPH Generator</a>
                @endunless
            </x-empty-state>
        @else
            <x-table>
                <thead>
                    <tr><th>RPH title</th><th>Subject</th><th>Class</th><th>Topic</th><th>Lesson date</th><th>Created</th><th>Last updated</th><th class="text-right">Actions</th></tr>
                </thead>
                <tbody>
                    @foreach ($documents as $document)
                        <tr>
                            <td><a href="{{ route('teacher.rph.show', $document) }}" class="link">{{ $document->title }}</a></td>
                            <td>{{ $document->subject?->name }}</td>
                            <td>{{ $document->schoolClass?->name }}</td>
                            <td>{{ $document->topic }}</td>
                            <td class="whitespace-nowrap">{{ $document->lesson_date->translatedFormat('j M Y') }}</td>
                            <td class="whitespace-nowrap">{{ $document->created_at->translatedFormat('j M Y') }}</td>
                            <td class="whitespace-nowrap">{{ $document->updated_at->translatedFormat('j M Y') }}</td>
                            <td class="text-right whitespace-nowrap">
                                <div class="inline-flex gap-3">
                                    <a href="{{ route('teacher.rph.show', $document) }}" class="link">View</a>
                                    <a href="{{ route('teacher.rph.edit', $document) }}" class="link">Edit</a>
                                    <a href="{{ route('teacher.rph.print', $document) }}" class="link" target="_blank">Print</a>
                                    <x-delete-button :action="route('teacher.rph.destroy', $document)" message="Delete this RPH? This cannot be undone." />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </x-table>
            <div class="border-t border-slate-100 p-4">{{ $documents->links() }}</div>
        @endif
    </x-card>
</x-layouts.app>
