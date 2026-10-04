<x-layouts.app title="Review RPH Draft">
    <x-page-header title="Review Your RPH Draft" description="Read through the draft and make any changes. Nothing is saved until you click Save RPH." />

    <x-ai-notice class="mb-6" />

    {{-- Regenerate re-sends the original lesson details --}}
    <form id="regenerate-form" method="POST" action="{{ route('teacher.rph.generate') }}" class="hidden">
        @csrf
        @foreach ($inputs as $field => $value)
            <input type="hidden" name="{{ $field }}" value="{{ $value }}">
        @endforeach
    </form>

    <form method="POST" action="{{ route('teacher.rph.store') }}" class="space-y-6">
        @csrf
        @include('teacher.rph._editor')

        <div class="card sticky bottom-0 flex flex-wrap items-center gap-3 p-4">
            <button type="submit" class="btn btn-primary" data-loading-text="Saving…"><x-icon name="check" /> Save RPH</button>
            <button type="submit" form="regenerate-form" class="btn btn-secondary" data-loading-text="Regenerating… please wait"><x-icon name="refresh" /> Regenerate</button>
            <a href="{{ route('teacher.rph.create', ['from' => 'draft']) }}" class="btn btn-secondary"><x-icon name="edit" /> Change lesson details</a>
        </div>
    </form>
</x-layouts.app>
