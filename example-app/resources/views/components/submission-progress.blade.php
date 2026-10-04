@props(['submitted', 'students'])

@php($rate = $students > 0 ? (int) round($submitted / $students * 100) : 0)

<div {{ $attributes }}>
    <div class="flex items-center justify-between text-sm">
        <span class="font-semibold text-slate-800">{{ $submitted }}/{{ $students }} submitted</span>
        <span class="text-slate-600">{{ $rate }}%</span>
    </div>
    <div class="mt-1 h-2.5 w-full overflow-hidden rounded-full bg-slate-200" role="progressbar" aria-valuenow="{{ $rate }}" aria-valuemin="0" aria-valuemax="100">
        <div @class(['h-full rounded-full', 'bg-green-500' => $rate >= 80, 'bg-amber-500' => $rate >= 50 && $rate < 80, 'bg-red-500' => $rate < 50]) style="width: {{ $rate }}%"></div>
    </div>
</div>
