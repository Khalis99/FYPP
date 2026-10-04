<x-layouts.app title="Upload Material">
    <x-page-header title="Upload Teaching Material" :back="route('teacher.materials.index')" />

    <x-card class="max-w-3xl">
        <form method="POST" action="{{ route('teacher.materials.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @include('teacher.materials._form')

            <div class="flex gap-3">
                <button type="submit" class="btn btn-primary" data-loading-text="Uploading…"><x-icon name="upload" /> Upload</button>
                <a href="{{ route('teacher.materials.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </x-card>
</x-layouts.app>
