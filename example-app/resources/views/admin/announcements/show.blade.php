<x-layouts.app title="Announcement">
    <x-page-header :title="$announcement->title" :back="route('admin.announcements.index')">
        <x-slot:actions>
            @unless ($announcement->isPublished())
                <form method="POST" action="{{ route('admin.announcements.publish', $announcement) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-primary"><x-icon name="announcements" /> Publish</button>
                </form>
            @endunless
            <a href="{{ route('admin.announcements.edit', $announcement) }}" class="btn btn-secondary"><x-icon name="edit" /> Edit</a>
            <x-delete-button :action="route('admin.announcements.destroy', $announcement)" button="btn btn-danger" message="Delete this announcement?" />
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-card class="lg:col-span-2">
            <div class="text-[17px] leading-relaxed whitespace-pre-line text-slate-800">{{ $announcement->body }}</div>
        </x-card>
        <x-card title="Details">
            <dl class="space-y-3 text-[15px]">
                <div><dt class="text-sm text-slate-500">Status</dt><dd><x-badge :color="$announcement->isPublished() ? 'green' : 'slate'">{{ $announcement->status->label() }}</x-badge></dd></div>
                <div><dt class="text-sm text-slate-500">Target audience</dt><dd>{{ $announcement->audience->label() }}</dd></div>
                <div><dt class="text-sm text-slate-500">Published</dt><dd>{{ $announcement->published_at?->format('j F Y, g:i A') ?? 'Not published' }}</dd></div>
                <div><dt class="text-sm text-slate-500">Author</dt><dd>{{ $announcement->author->name }}</dd></div>
                <div><dt class="text-sm text-slate-500">Read by</dt><dd>{{ $announcement->readers_count }} user(s)</dd></div>
            </dl>
        </x-card>
    </div>
</x-layouts.app>
