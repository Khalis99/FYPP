<x-layouts.app title="New Student">
    <x-page-header title="New Student" :back="route('admin.students.index')" />

    <x-card class="max-w-3xl">
        <form method="POST" action="{{ route('admin.students.store') }}" class="space-y-6">
            @csrf
            @include('admin.students._form')
            <div class="flex gap-3">
                <button type="submit" class="btn btn-primary">Create Student</button>
                <a href="{{ route('admin.students.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </x-card>
</x-layouts.app>
