<x-layouts.app title="Edit Assignment">
    <x-page-header title="Edit Assignment" :back="route('teacher.assignments.show', $assignment)" />

    <x-card class="max-w-3xl">
        <form method="POST" action="{{ route('teacher.assignments.update', $assignment) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            @include('teacher.assignments._form')

            <div class="flex gap-3">
                <button type="submit" class="btn btn-primary" data-loading-text="Saving…">Save Changes</button>
                <a href="{{ route('teacher.assignments.show', $assignment) }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </x-card>
</x-layouts.app>
