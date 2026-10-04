<x-layouts.app title="Edit Material">
    <x-page-header title="Edit Teaching Material" :back="route('teacher.materials.show', $material)" />

    <x-card class="max-w-3xl">
        <form method="POST" action="{{ route('teacher.materials.update', $material) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            @include('teacher.materials._form')

            <div class="flex gap-3">
                <button type="submit" class="btn btn-primary" data-loading-text="Saving…">Save Changes</button>
                <a href="{{ route('teacher.materials.show', $material) }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </x-card>
</x-layouts.app>
