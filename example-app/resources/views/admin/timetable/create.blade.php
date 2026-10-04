<x-layouts.app title="New Timetable Entry">
    <x-page-header title="New Timetable Entry" :back="route('admin.timetable.index')" />

    <x-card class="max-w-3xl">
        <form method="POST" action="{{ route('admin.timetable.store') }}" class="space-y-6">
            @csrf
            @include('admin.timetable._form')
            <div class="flex gap-3">
                <button type="submit" class="btn btn-primary">Add Entry</button>
                <a href="{{ route('admin.timetable.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </x-card>
</x-layouts.app>
