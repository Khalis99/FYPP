<x-layouts.app title="Announcements">
    <x-page-header title="Announcements" description="News and notices from the school administration." />

    <form method="GET" class="card mb-6 grid gap-4 p-4 sm:grid-cols-3 sm:items-end">
        <x-form.input name="search" label="Search announcements" :value="$filters['search'] ?? ''" />
        <x-form.select name="read" label="Show" :options="['unread' => 'Unread only']" :value="$filters['read'] ?? ''" placeholder="All announcements" />
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary"><x-icon name="search" /> Search</button>
            @if (array_filter($filters))
                <a href="{{ route('teacher.announcements.index') }}" class="btn btn-secondary">Clear</a>
            @endif
        </div>
    </form>

    <div class="space-y-4">
        @forelse ($announcements as $announcement)
            @php($isRead = $readIds->contains($announcement->id))
            <a href="{{ route('teacher.announcements.show', $announcement) }}" @class(['card block p-5 hover:border-brand-200', 'border-l-4 border-l-brand-500' => ! $isRead])>
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <h3 class="text-lg font-semibold text-slate-900">{{ $announcement->title }}</h3>
                    @if ($isRead)
                        <x-badge>Read</x-badge>
                    @else
                        <x-badge color="blue">New</x-badge>
                    @endif
                </div>
                <p class="mt-1 line-clamp-2 text-[16px] text-slate-700">{{ $announcement->body }}</p>
                <p class="mt-2 text-sm text-slate-500">{{ $announcement->published_at->format('j F Y') }} · {{ $announcement->author->name }}</p>
            </a>
        @empty
            <div class="card"><x-empty-state icon="announcements" title="No announcements found" /></div>
        @endforelse
    </div>

    <div class="mt-6">{{ $announcements->links() }}</div>
</x-layouts.app>
