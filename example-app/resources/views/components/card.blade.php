@props(['title' => null, 'padding' => true])

<section {{ $attributes->merge(['class' => 'card']) }}>
    @if ($title || isset($actions))
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
            @if ($title)
                <h3 class="text-lg font-semibold text-slate-900">{{ $title }}</h3>
            @endif
            @isset($actions)
                <div class="flex flex-wrap gap-2">{{ $actions }}</div>
            @endisset
        </div>
    @endif

    <div @class(['p-5' => $padding])>
        {{ $slot }}
    </div>
</section>
