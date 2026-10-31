{{-- i18n:off --}}
@props(['label', 'value', 'icon', 'color' => 'blue', 'href' => null])

@php
    $tints = [
        'blue' => 'bg-teal-50 text-teal-700',
        'green' => 'bg-green-50 text-green-600',
        'amber' => 'bg-amber-50 text-amber-600',
        'purple' => 'bg-purple-50 text-purple-600',
        'red' => 'bg-red-50 text-red-600',
    ];
@endphp

<{{ $href ? 'a' : 'div' }} @if ($href) href="{{ $href }}" @endif class="card card-lift group flex items-center justify-between gap-4 p-5">
    <div>
        <p class="text-[15px] font-semibold text-slate-600">{{ $label }}</p>
        <p class="mt-1 text-3xl font-extrabold text-slate-900" data-countup>{{ $value }}</p>
    </div>
    <span class="rounded-2xl p-3 transition duration-300 group-hover:-rotate-6 group-hover:scale-110 {{ $tints[$color] ?? $tints['blue'] }}">
        <x-icon :name="$icon" class="size-7" />
    </span>
</{{ $href ? 'a' : 'div' }}>