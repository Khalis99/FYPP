@php
    $messages = array_filter([
        'success' => session('success') ?? session('status'),
        'error' => session('error'),
    ]);
@endphp

<div class="no-print pointer-events-none fixed top-24 right-4 z-50 flex w-[min(24rem,calc(100vw-2rem))] flex-col gap-3" aria-live="polite">
    @foreach ($messages as $type => $message)
        <div data-toast role="status" @class([
            'pointer-events-auto flex items-start gap-3 rounded-2xl border bg-white p-4 shadow-lift',
            'border-green-200' => $type === 'success',
            'border-red-200' => $type === 'error',
        ])>
            <x-icon :name="$type === 'success' ? 'check' : 'warning'" @class(['size-6', 'text-green-600' => $type === 'success', 'text-red-600' => $type === 'error']) />
            <p class="flex-1 text-[15px] text-slate-800">{{ $message }}</p>
            <button type="button" class="text-slate-400 hover:text-slate-600" data-toast-close aria-label="Close">
                <x-icon name="x" />
            </button>
        </div>
    @endforeach
</div>
