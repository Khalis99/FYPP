<x-layouts.app title="New Announcement">
    <x-page-header title="New Announcement" description="Publishing sends a notification to everyone in the target audience." :back="route('admin.announcements.index')" />

    <x-card class="max-w-3xl">
        <form method="POST" action="{{ route('admin.announcements.store') }}" class="space-y-5">
            @csrf
            @include('admin.announcements._form')
        </form>
    </x-card>
</x-layouts.app>
