<x-layouts.app :title="$document->title">
    <x-page-header :title="$document->title" :description="__('Last updated :date', ['date' => $document->updated_at->translatedFormat('j M Y, g:i A')])" :back="route('teacher.rph.index')">
        <x-slot:actions>
            <a href="{{ route('teacher.rph.edit', $document) }}" class="btn btn-primary"><x-icon name="edit" /> Edit</a>
            <a href="{{ route('teacher.rph.print', $document) }}" class="btn btn-secondary" target="_blank"><x-icon name="printer" /> Print</a>
            <a href="{{ route('teacher.rph.download', $document) }}" class="btn btn-secondary"><x-icon name="download" /> Download (Word)</a>
            <x-delete-button :action="route('teacher.rph.destroy', $document)" button="btn btn-danger" message="Delete this RPH? This cannot be undone." />
        </x-slot:actions>
    </x-page-header>

    @if ($document->is_ai_generated)
        <x-ai-notice class="mb-6" />
    @endif

    @include('teacher.rph._document-styles')
    <div class="card mx-auto max-w-4xl p-6 sm:p-10">
        @include('teacher.rph._document')
    </div>
</x-layouts.app>
