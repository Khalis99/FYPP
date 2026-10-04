<x-layouts.app title="Edit Announcement">
    <x-page-header title="Edit Announcement" :back="route('admin.announcements.show', $announcement)" />

    <x-card class="max-w-3xl">
        <form method="POST" action="{{ route('admin.announcements.update', $announcement) }}" class="space-y-5">
            @csrf
            @method('PUT')
            @include('admin.announcements._form')
        </form>
    </x-card>
</x-layouts.app>
