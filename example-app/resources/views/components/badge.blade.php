@props(['color' => 'slate'])

@php
    $colors = [
        'slate' => 'bg-slate-100 text-slate-700',
        'blue' => 'bg-teal-100 text-teal-800',
        'green' => 'bg-green-100 text-green-800',
        'amber' => 'bg-amber-100 text-amber-800',
        'red' => 'bg-red-100 text-red-800',
        'purple' => 'bg-purple-100 text-purple-800',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-0.5 text-sm font-semibold whitespace-nowrap '.($colors[$color] ?? $colors['slate'])]) }}>
    {{ $slot }}
</span>
