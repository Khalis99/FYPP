<x-layouts.app title="Announcement">
    <x-page-header :title="$announcement->title" :description="$announcement->published_at->translatedFormat('l, j F Y').' · '.$announcement->author->name" :back="route('teacher.announcements.index')" />

    <x-card class="max-w-3xl">
        <div class="text-[17px] leading-relaxed whitespace-pre-line text-slate-800">{{ $announcement->body }}</div>

        <div class="mt-6 border-t border-slate-100 pt-5">
            @if ($isRead)
                <p class="inline-flex items-center gap-2 font-semibold text-green-700"><x-icon name="check" /> You have read this announcement.</p>
            @else
                <form method="POST" action="{{ route('teacher.announcements.read', $announcement) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary"><x-icon name="check" /> Mark as read</button>
                </form>
            @endif
        </div>
    </x-card>
</x-layouts.app>
