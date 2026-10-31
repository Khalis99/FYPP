<x-layouts.app title="Edit Teacher">
    <x-page-header :title="__('Edit :name', ['name' => $user->name])" :back="route('admin.teachers.show', $teacher)" />

    <x-card class="max-w-3xl">
        <form method="POST" action="{{ route('admin.teachers.update', $teacher) }}" class="space-y-6">
            @csrf
            @method('PUT')
            @include('admin.teachers._form')
            <div class="flex gap-3">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('admin.teachers.show', $teacher) }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </x-card>
</x-layouts.app>
