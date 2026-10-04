@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title.' · ' : '' }}SchoolHub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-900 antialiased">
    <div class="flex min-h-screen flex-col lg:flex-row">
        <section class="flex flex-col justify-between bg-brand-700 px-8 py-8 text-white lg:w-[42%] lg:px-14 lg:py-14">
            <div class="flex items-center gap-3">
                <x-icon name="classes" class="size-10" />
                <div class="leading-tight">
                    <p class="text-2xl font-bold">SchoolHub</p>
                    <p class="text-sm text-brand-100">{{ config('schoolhub.tagline') }}</p>
                </div>
            </div>

            <div class="hidden lg:block">
                <p class="text-3xl leading-snug font-semibold">Everything you need for teaching, in one place.</p>
                <ul class="mt-8 space-y-4 text-lg text-brand-100">
                    <li class="flex items-center gap-3"><x-icon name="materials" class="size-6 text-white" /> Teaching materials and assignments</li>
                    <li class="flex items-center gap-3"><x-icon name="timetable" class="size-6 text-white" /> Your timetable and class lists</li>
                    <li class="flex items-center gap-3"><x-icon name="sparkles" class="size-6 text-white" /> AI-assisted RPH preparation</li>
                </ul>
            </div>

            <p class="hidden text-sm text-brand-200 lg:block">{{ config('schoolhub.school_name') }}</p>
        </section>

        <main class="flex flex-1 items-center justify-center px-4 py-10 sm:px-8">
            <div class="w-full max-w-md">
                {{ $slot }}
            </div>
        </main>
    </div>

    <x-toasts />
</body>
</html>
