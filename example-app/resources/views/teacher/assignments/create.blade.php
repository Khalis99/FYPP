<x-layouts.app title="Create Assignment">
    <x-page-header title="Create Assignment" :back="route('teacher.assignments.index')" />

    <x-card class="max-w-3xl">
        <form method="POST" action="{{ route('teacher.assignments.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @include('teacher.assignments._form')

            <div class="flex gap-3">
                <button type="submit" class="btn btn-primary" data-loading-text="Saving…">Create Assignment</button>
                <a href="{{ route('teacher.assignments.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </x-card>
</x-layouts.app>
