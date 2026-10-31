{{-- Quick "jump to" palette. Opened with Ctrl/Cmd+K or "/", filtered and navigated by app.js. --}}
@props(['pages' => [], 'actions' => []])

<dialog id="palette" class="mx-auto mt-[12vh] w-[min(36rem,calc(100vw-2rem))] overflow-hidden rounded-3xl border border-slate-200 p-0 shadow-lift" aria-label="Jump to a page">
    <div class="flex items-center gap-3 border-b border-slate-200 px-5 py-4">
        <x-icon name="search" class="size-5 text-brand-600" />
        <input type="text" data-palette-input class="w-full bg-transparent text-[17px] font-semibold text-slate-900 placeholder:text-slate-400 focus:outline-none" placeholder="Where would you like to go?" autocomplete="off" aria-label="Search pages and actions">
        <kbd class="rounded-md border border-slate-200 px-1.5 py-0.5 text-xs font-bold text-slate-500">Esc</kbd>
    </div>

    <div class="max-h-[22rem] overflow-y-auto p-2">
        @foreach (['Go to' => $pages, 'Quick actions' => $actions] as $heading => $items)
            @if (count($items))
                <div data-palette-group class="mb-1">
                    <p class="px-3 pt-2 pb-1 text-xs font-extrabold tracking-wider text-slate-500 uppercase">{{ $heading }}</p>
                    @foreach ($items as $item)
                        <a href="{{ route($item['route']) }}" class="palette-item" data-palette-item data-text="{{ strtolower($item['label']) }}">
                            <x-icon :name="$item['icon']" class="size-5 text-brand-600" />
                            <span>{{ $item['label'] }}</span>
                            <span class="palette-hint">Open <x-icon name="arrow-right" class="inline size-3.5" /></span>
                        </a>
                    @endforeach
                </div>
            @endif
        @endforeach

        <p data-palette-empty hidden class="px-4 py-8 text-center text-[15px] text-slate-500">Nothing matches that. Try another word.</p>
    </div>

    <div class="flex items-center gap-4 border-t border-slate-200 bg-slate-50/70 px-5 py-2.5 text-xs font-semibold text-slate-500">
        <span><kbd class="font-bold">↑↓</kbd> to move</span>
        <span><kbd class="font-bold">Enter</kbd> to open</span>
    </div>
</dialog>
