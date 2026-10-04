@props(['title', 'description' => null, 'back' => null])

<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        @if ($back)
            <a href="{{ $back }}" class="mb-2 inline-flex items-center gap-1.5 text-[15px] link">
                <x-icon name="back" class="size-4" /> Back
            </a>
        @endif
        <h2 class="text-2xl font-bold text-slate-900 sm:text-3xl">{{ $title }}</h2>
        @if ($description)
            <p class="mt-1 text-[16px] text-slate-600">{{ $description }}</p>
        @endif
    </div>

    @if (isset($actions))
        <div class="flex flex-wrap gap-2">{{ $actions }}</div>
    @endif
</div>
