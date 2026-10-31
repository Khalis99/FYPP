<x-layouts.app title="Edit Class">
    <x-page-header :title="__('Edit :name', ['name' => $schoolClass->name])" :back="route('admin.classes.show', $schoolClass)" />

    <x-card class="max-w-3xl">
        <form method="POST" action="{{ route('admin.classes.update', $schoolClass) }}" class="space-y-6">
            @csrf
            @method('PUT')
            <div class="grid gap-5 sm:grid-cols-3">
                @include('admin.classes._fields')
            </div>
            <div class="flex gap-3">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('admin.classes.show', $schoolClass) }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </x-card>
</x-layouts.app>
