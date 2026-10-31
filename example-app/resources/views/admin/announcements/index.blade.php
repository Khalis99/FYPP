<x-layouts.app title="Announcements">
    <x-page-header title="Announcements" description="Create and publish notices for teachers and staff.">
        <x-slot:actions>
            <a href="{{ route('admin.announcements.create') }}" class="btn btn-primary"><x-icon name="plus" /> New Announcement</a>
        </x-slot:actions>
    </x-page-header>

    <form method="GET" class="card mb-6 grid gap-4 p-4 sm:grid-cols-3 sm:items-end">
        <x-form.input name="search" label="Search title" :value="$filters['search'] ?? ''" />
        <x-form.select name="status" label="Status" :options="$statusOptions" :value="$filters['status'] ?? ''" placeholder="All" />
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary"><x-icon name="search" /> Search</button>
            @if (array_filter($filters))
                <a href="{{ route('admin.announcements.index') }}" class="btn btn-secondary">Clear</a>
            @endif
        </div>
    </form>

    <x-card :padding="false">
        <x-table>
            <thead><tr><th>Title</th><th>Audience</th><th>Status</th><th>Date</th><th>Read by</th><th class="text-right">Actions</th></tr></thead>
            <tbody>
                @forelse ($announcements as $announcement)
                    <tr>
                        <td><a href="{{ route('admin.announcements.show', $announcement) }}" class="link">{{ $announcement->title }}</a></td>
                        <td>{{ $announcement->audience->label() }}</td>
                        <td><x-badge :color="$announcement->isPublished() ? 'green' : 'slate'">{{ $announcement->status->label() }}</x-badge></td>
                        <td class="whitespace-nowrap">{{ ($announcement->published_at ?? $announcement->created_at)->translatedFormat('j M Y') }}</td>
                        <td>{{ $announcement->readers_count }}</td>
                        <td class="text-right whitespace-nowrap">
                            <div class="inline-flex gap-3">
                                @unless ($announcement->isPublished())
                                    <form method="POST" action="{{ route('admin.announcements.publish', $announcement) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="link">Publish</button>
                                    </form>
                                @endunless
                                <a href="{{ route('admin.announcements.edit', $announcement) }}" class="link">Edit</a>
                                <x-delete-button :action="route('admin.announcements.destroy', $announcement)" message="Delete this announcement?" />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-slate-500">No announcements found.</td></tr>
                @endforelse
            </tbody>
        </x-table>
        <div class="border-t border-slate-100 p-4">{{ $announcements->links() }}</div>
    </x-card>
</x-layouts.app>
