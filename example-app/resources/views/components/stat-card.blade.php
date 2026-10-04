@props(['label', 'value', 'icon', 'color' => 'blue', 'href' => null])

@php
    $tints = [
        'blue' => 'bg-blue-50 text-blue-600',
        'green' => 'bg-green-50 text-green-600',
        'amber' => 'bg-amber-50 text-amber-600',
        'purple' => 'bg-purple-50 text-purple-600',
        'red' => 'bg-red-50 text-red-600',
    ];
@endphp

<{{ $href ? 'a' : 'div' }} @if ($href) href="{{ $href }}" @endif class="card flex items-center justify-between gap-4 p-5 {{ $href ? 'hover:border-brand-200 hover:shadow' : '' }}">
    <div>
        <p class="text-[15px] text-slate-600">{{ $label }}</p>
        <p class="mt-1 text-3xl font-bold text-slate-900">{{ $value }}</p>
    </div>
    <span class="rounded-lg p-3 {{ $tints[$color] ?? $tints['blue'] }}">
        <x-icon :name="$icon" class="size-7" />
    </span>
</{{ $href ? 'a' : 'div' }}>
