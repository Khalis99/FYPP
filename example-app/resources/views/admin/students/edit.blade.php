<x-layouts.app title="Edit Student">
    <x-page-header :title="__('Edit :name', ['name' => $student->name])" :back="route('admin.students.show', $student)" />

    <x-card class="max-w-3xl">
        <form method="POST" action="{{ route('admin.students.update', $student) }}" class="space-y-6">
            @csrf
            @method('PUT')
            @include('admin.students._form')
            <div class="flex gap-3">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('admin.students.show', $student) }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </x-card>
</x-layouts.app>
