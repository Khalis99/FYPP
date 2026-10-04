@props(['icon' => 'info', 'title', 'message' => null])

<div class="flex flex-col items-center px-6 py-12 text-center">
    <span class="rounded-full bg-slate-100 p-4 text-slate-500"><x-icon :name="$icon" class="size-8" /></span>
    <p class="mt-4 text-lg font-semibold text-slate-800">{{ $title }}</p>
    @if ($message)
        <p class="mt-1 max-w-md text-[15px] text-slate-600">{{ $message }}</p>
    @endif
    @if (! $slot->isEmpty())
        <div class="mt-5">{{ $slot }}</div>
    @endif
</div>
