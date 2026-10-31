<x-layouts.app title="Teaching Materials">
    <x-page-header title="Teaching Materials" description="Upload, organise and download your notes, slides and worksheets.">
        <x-slot:actions>
            <a href="{{ route('teacher.materials.create') }}" class="btn btn-primary"><x-icon name="upload" /> Upload Material</a>
        </x-slot:actions>
    </x-page-header>

    <form method="GET" class="card mb-6 grid gap-4 p-4 sm:grid-cols-2 lg:grid-cols-5 lg:items-end">
        <div class="lg:col-span-2">
            <x-form.input name="search" label="Search title or topic" :value="$filters['search'] ?? ''" placeholder="e.g. Algebra" />
        </div>
        <x-form.select name="subject_id" label="Subject" :options="$subjectOptions" :value="$filters['subject_id'] ?? ''" placeholder="All subjects" />
        <x-form.select name="school_class_id" label="Class" :options="$classOptions" :value="$filters['school_class_id'] ?? ''" placeholder="All classes" />
        <x-form.select name="type" label="Type" :options="$typeOptions" :value="$filters['type'] ?? ''" placeholder="All types" />
        <div class="flex gap-2 sm:col-span-2 lg:col-span-5">
            <button type="submit" class="btn btn-primary"><x-icon name="search" /> Search</button>
            @if (array_filter($filters))
                <a href="{{ route('teacher.materials.index') }}" class="btn btn-secondary">Clear filters</a>
            @endif
        </div>
    </form>

    <x-card :padding="false">
        @if ($materials->isEmpty())
            <x-empty-state icon="materials" title="No materials found" message="{{ array_filter($filters) ? __('Try changing your search or filters.') : __('Upload your first teaching material to get started.') }}" />
        @else
            <x-table>
                <thead>
                    <tr><th>Title</th><th>Subject</th><th>Class</th><th>Type</th><th>Uploaded</th><th class="text-right">Actions</th></tr>
                </thead>
                <tbody>
                    @foreach ($materials as $material)
                        <tr>
                            <td>
                                <a href="{{ route('teacher.materials.show', $material) }}" class="link">{{ $material->title }}</a>
                                @if ($material->topic)<span class="block text-sm text-slate-500">{{ $material->topic }}</span>@endif
                            </td>
                            <td>{{ $material->subject->name }}</td>
                            <td>{{ $material->schoolClass?->name ?? __('All classes') }}</td>
                            <td><x-badge>{{ $material->type->label() }}</x-badge></td>
                            <td class="whitespace-nowrap">{{ $material->created_at->translatedFormat('j M Y') }}</td>
                            <td class="text-right whitespace-nowrap">
                                <div class="inline-flex gap-3">
                                    <a href="{{ route('teacher.materials.show', $material) }}" class="link">View</a>
                                    <a href="{{ route('teacher.materials.download', $material) }}" class="link">Download</a>
                                    <a href="{{ route('teacher.materials.edit', $material) }}" class="link">Edit</a>
                                    <x-delete-button :action="route('teacher.materials.destroy', $material)" :message="__('Delete \':title\'? The file will be removed permanently.', ['title' => $material->title])" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </x-table>
            <div class="border-t border-slate-100 p-4">{{ $materials->links() }}</div>
        @endif
    </x-card>
</x-layouts.app>
