<x-layouts.app title="Edit Timetable Entry">
    <x-page-header title="Edit Timetable Entry" :back="route('admin.timetable.index')" />

    <x-card class="max-w-3xl">
        <form method="POST" action="{{ route('admin.timetable.update', $timetable) }}" class="space-y-6">
            @csrf
            @method('PUT')
            @include('admin.timetable._form')
            <div class="flex gap-3">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('admin.timetable.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </x-card>
</x-layouts.app>
