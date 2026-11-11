@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title.' · ' : '' }}Pelita</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <script>
        try { document.documentElement.dataset.theme = localStorage.getItem('pelita-theme') || 'light'; } catch (e) {}
    </script>
    <x-js-strings />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-sans text-slate-900 antialiased">
    <div class="aurora flex min-h-screen items-center justify-center overflow-hidden px-4 py-10 sm:px-8" data-aurora>
        {{-- Drifting colour, and embers that rise like sparks from a lantern --}}
        <span class="blob -top-32 -left-24 size-[28rem] bg-orange-500" aria-hidden="true"></span>
        <span class="blob -right-32 -bottom-40 size-[32rem] bg-rose-600" style="animation-delay: -6s" aria-hidden="true"></span>
        <span class="blob top-1/3 left-1/2 size-80 bg-amber-400 opacity-30" style="animation-delay: -11s" aria-hidden="true"></span>
        @foreach (range(1, 18) as $i)
            <span class="ember" aria-hidden="true" style="--x: {{ ($i * 53) % 97 + 1 }}%; --s: {{ 4 + ($i * 7) % 9 }}px; --d: {{ 9 + ($i * 5) % 9 }}s; --delay: -{{ ($i * 3) % 14 }}s; --dx: {{ (($i * 29) % 80) - 40 }}px"></span>
        @endforeach

        <div class="absolute top-4 right-4 z-20 sm:top-6 sm:right-8">
            <x-language-switch />
        </div>

        <div class="relative z-10 mx-auto grid w-full max-w-6xl items-center gap-10 lg:grid-cols-[1.1fr_0.9fr] lg:gap-16">
            <section class="text-white">
                <div class="flex items-center gap-3">
                    <div class="leading-tight">
                        <p class="text-6xl font-extrabold tracking-wide sm:text-7xl">Pelita</p>
                        <p class="mt-1 text-base font-semibold text-orange-100 sm:text-lg">{{ __(config('schoolhub.tagline')) }}</p>
                    </div>
                </div>

                <div class="hidden lg:mt-24 lg:block">
                    <p class="max-w-lg text-5xl leading-[1.1] font-extrabold">Plan smarter. Teach with joy.</p>
                    <p class="mt-8 text-sm font-bold text-orange-200">{{ config('schoolhub.school_name') }}</p>
                </div>
            </section>

            <main class="w-full max-w-md justify-self-center lg:justify-self-end">
                {{ $slot }}
            </main>
        </div>
    </div>

    <x-toasts />
</body>
</html>
