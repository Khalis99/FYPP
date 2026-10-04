<x-layouts.app title="Edit RPH">
    <x-page-header title="Edit RPH" :back="route('teacher.rph.show', $document)" />

    @if ($document->is_ai_generated)
        <x-ai-notice class="mb-6" />
    @endif

    <form method="POST" action="{{ route('teacher.rph.update', $document) }}" class="space-y-6">
        @csrf
        @method('PUT')
        @include('teacher.rph._editor')

        <div class="card sticky bottom-0 flex flex-wrap gap-3 p-4">
            <button type="submit" class="btn btn-primary" data-loading-text="Saving…"><x-icon name="check" /> Save RPH</button>
            <a href="{{ route('teacher.rph.show', $document) }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</x-layouts.app>
