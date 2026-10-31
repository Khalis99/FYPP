<x-layouts.app :title="$material->title">
    <x-page-header :title="$material->title" :back="route('teacher.materials.index')">
        <x-slot:actions>
            <a href="{{ route('teacher.materials.download', $material) }}" class="btn btn-primary"><x-icon name="download" /> Download</a>
            <a href="{{ route('teacher.materials.edit', $material) }}" class="btn btn-secondary"><x-icon name="edit" /> Edit</a>
            <x-delete-button :action="route('teacher.materials.destroy', $material)" button="btn btn-danger" message="Delete this material? The file will be removed permanently." />
        </x-slot:actions>
    </x-page-header>

    <x-card class="max-w-3xl">
        <dl class="grid gap-x-6 gap-y-5 sm:grid-cols-2">
            <div><dt class="text-sm text-slate-500">Subject</dt><dd class="text-[17px] font-medium">{{ $material->subject->name }}</dd></div>
            <div><dt class="text-sm text-slate-500">Class</dt><dd class="text-[17px] font-medium">{{ $material->schoolClass?->name ?? __('All my classes') }}</dd></div>
            <div><dt class="text-sm text-slate-500">Topic</dt><dd class="text-[17px]">{{ $material->topic ?: '—' }}</dd></div>
            <div><dt class="text-sm text-slate-500">Type</dt><dd><x-badge>{{ $material->type->label() }}</x-badge></dd></div>
            <div><dt class="text-sm text-slate-500">Uploaded</dt><dd class="text-[17px]">{{ $material->created_at->translatedFormat('j F Y, g:i A') }}</dd></div>
            <div><dt class="text-sm text-slate-500">File</dt><dd class="text-[17px] break-all">{{ $material->file_name }} <span class="text-sm text-slate-500">({{ $material->readableFileSize() }})</span></dd></div>
            <div class="sm:col-span-2"><dt class="text-sm text-slate-500">Description</dt><dd class="text-[17px] whitespace-pre-line">{{ $material->description ?: '—' }}</dd></div>
        </dl>
    </x-card>
</x-layouts.app>
