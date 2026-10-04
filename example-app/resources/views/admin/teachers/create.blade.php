<x-layouts.app title="New Teacher">
    <x-page-header title="New Teacher" description="Creates a login account and teacher profile." :back="route('admin.teachers.index')" />

    <x-card class="max-w-3xl">
        <form method="POST" action="{{ route('admin.teachers.store') }}" class="space-y-6">
            @csrf
            @include('admin.teachers._form')
            <div class="flex gap-3">
                <button type="submit" class="btn btn-primary">Create Teacher</button>
                <a href="{{ route('admin.teachers.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </x-card>
</x-layouts.app>
