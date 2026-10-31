@props(['title' => null])

@php
    $user = auth()->user();

    $navigation = $user->isAdmin()
        ? [
            ['route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'icon' => 'dashboard', 'label' => __('Dashboard')],
            ['route' => 'admin.users.index', 'active' => 'admin.users.*', 'icon' => 'users', 'label' => __('Users')],
            ['route' => 'admin.teachers.index', 'active' => 'admin.teachers.*', 'icon' => 'teachers', 'label' => __('Teachers')],
            ['route' => 'admin.students.index', 'active' => 'admin.students.*', 'icon' => 'students', 'label' => __('Students')],
            ['route' => 'admin.classes.index', 'active' => ['admin.classes.*', 'admin.subjects.*'], 'icon' => 'classes', 'label' => __('Classes')],
            ['route' => 'admin.announcements.index', 'active' => 'admin.announcements.*', 'icon' => 'announcements', 'label' => __('Announcements')],
            ['route' => 'admin.timetable.index', 'active' => 'admin.timetable.*', 'icon' => 'timetable', 'label' => __('Timetable')],
            ['route' => 'profile.show', 'active' => 'profile.*', 'icon' => 'profile', 'label' => __('Profile')],
        ]
        : [
            ['route' => 'teacher.dashboard', 'active' => 'teacher.dashboard', 'icon' => 'dashboard', 'label' => __('Dashboard')],
            ['route' => 'teacher.classes.index', 'active' => 'teacher.classes.*', 'icon' => 'classes', 'label' => __('My Classes')],
            ['route' => 'teacher.materials.index', 'active' => 'teacher.materials.*', 'icon' => 'materials', 'label' => __('Teaching Materials')],
            ['route' => 'teacher.assignments.index', 'active' => ['teacher.assignments.*', 'teacher.submissions.*'], 'icon' => 'assignments', 'label' => __('Assignments')],
            ['route' => 'teacher.students.index', 'active' => 'teacher.students.*', 'icon' => 'students', 'label' => __('Student Records')],
            ['route' => 'teacher.timetable.index', 'active' => 'teacher.timetable.*', 'icon' => 'timetable', 'label' => __('Timetable')],
            ['route' => 'teacher.announcements.index', 'active' => 'teacher.announcements.*', 'icon' => 'announcements', 'label' => __('Announcements')],
            ['route' => 'teacher.rph.create', 'active' => ['teacher.rph.create', 'teacher.rph.generate', 'teacher.rph.draft'], 'icon' => 'sparkles', 'label' => __('RPH Generator')],
            ['route' => 'teacher.rph.index', 'active' => ['teacher.rph.index', 'teacher.rph.show', 'teacher.rph.edit'], 'icon' => 'document', 'label' => __('My RPH')],
            ['route' => 'profile.show', 'active' => 'profile.*', 'icon' => 'profile', 'label' => __('Profile')],
        ];

    $quickActions = $user->isAdmin()
        ? [
            ['route' => 'admin.classes.create', 'icon' => 'plus', 'label' => __('New class')],
            ['route' => 'admin.users.create', 'icon' => 'plus', 'label' => __('New user')],
            ['route' => 'admin.announcements.create', 'icon' => 'announcements', 'label' => __('New announcement')],
        ]
        : [
            ['route' => 'teacher.rph.create', 'icon' => 'sparkles', 'label' => __('Generate RPH')],
            ['route' => 'teacher.materials.create', 'icon' => 'upload', 'label' => __('Upload material')],
            ['route' => 'teacher.assignments.create', 'icon' => 'plus', 'label' => __('Create assignment')],
        ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title.' · ' : '' }}Pelita</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <script>
        try { document.documentElement.dataset.theme = localStorage.getItem('pelita-theme') || 'light'; } catch (e) {}
    </script>
    <x-js-strings />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-sans text-base text-slate-900 antialiased">
    {{-- Mobile backdrop --}}
    <div id="sidebar-backdrop" class="fixed inset-0 z-30 hidden bg-black/40 backdrop-blur-sm lg:hidden" data-sidebar-toggle></div>

    {{-- Floating glass sidebar --}}
    <aside id="sidebar" class="glass-panel no-print fixed inset-y-3 left-3 z-40 flex w-68 flex-col rounded-3xl">
        <a href="{{ route($user->role->homeRoute()) }}" class="group flex items-center gap-3 px-5 pt-5 pb-4">
            <span class="leading-tight">
                <span class="wordmark block text-2xl font-extrabold tracking-wide">Pelita</span>
                <span class="mt-0.5 inline-block rounded-full bg-brand-100 px-2 py-0.5 text-xs font-bold text-brand-800">{{ $user->isAdmin() ? __('Admin Portal') : __('Teacher Portal') }}</span>
            </span>
        </a>

        <nav class="flex-1 overflow-y-auto py-2" aria-label="Main navigation">
            @foreach ($navigation as $item)
                <x-sidebar-link :href="route($item['route'])" :active="request()->routeIs(...(array) $item['active'])" :icon="$item['icon']">
                    {{ $item['label'] }}
                </x-sidebar-link>
            @endforeach
        </nav>

        <form method="POST" action="{{ route('logout') }}" class="border-t border-slate-200 p-3">
            @csrf
            <button type="submit" class="group flex w-full items-center gap-4 rounded-2xl px-4 py-3 text-left text-[16px] font-semibold text-slate-700 transition duration-200 hover:bg-red-50 hover:text-red-700">
                <x-icon name="logout" class="size-6 text-slate-500 transition duration-300 group-hover:translate-x-0.5 group-hover:text-red-600" />
                Logout
            </button>
        </form>
    </aside>

    <div class="lg:pl-[18.5rem]">
        {{-- Floating top bar --}}
        <header class="glass-panel no-print sticky top-3 z-20 mx-3 mt-3 flex h-16 items-center gap-2 rounded-2xl px-3 sm:mx-5 sm:px-4 lg:mx-8">
            <button type="button" class="icon-btn lg:hidden" data-sidebar-toggle aria-label="Open menu">
                <x-icon name="menu" class="size-6" />
            </button>

            <h1 class="truncate text-lg font-extrabold text-slate-800 max-sm:sr-only">{{ $title }}</h1>

            <div class="ml-auto flex items-center gap-1 sm:gap-2">
                <button type="button" data-palette-open class="hidden items-center gap-3 rounded-xl border border-slate-200 bg-slate-50/70 px-3.5 py-2 text-sm font-semibold text-slate-500 transition duration-200 hover:border-brand-200 hover:text-brand-700 md:flex" aria-label="Search pages and actions">
                    <x-icon name="search" class="size-4" />
                    <span>Jump to…</span>
                    <kbd class="rounded-md border border-slate-300 px-1.5 text-xs font-bold">Ctrl K</kbd>
                </button>
                <button type="button" data-palette-open class="icon-btn md:hidden" aria-label="Search pages and actions">
                    <x-icon name="search" class="size-6" />
                </button>

                <x-language-switch />

                <button type="button" data-theme-toggle class="icon-btn" aria-label="Switch between light and dark mode">
                    <x-icon name="moon" class="icon-moon size-6" />
                    <x-icon name="sun" class="icon-sun size-6" />
                </button>

                <x-notification-bell :user="$user" />

                <div class="relative" data-dropdown>
                    <button type="button" class="flex items-center gap-2 rounded-full py-1 pr-2 pl-1 transition duration-200 hover:bg-brand-50" data-dropdown-trigger aria-expanded="false" aria-haspopup="true">
                        <x-avatar :user="$user" class="size-9 text-sm ring-2 ring-brand-200" />
                        <span class="hidden text-left leading-tight sm:block">
                            <span class="block text-sm font-bold text-slate-800">{{ $user->name }}</span>
                            <span class="block text-xs text-slate-500">{{ $user->role->label() }}</span>
                        </span>
                        <x-icon name="chevron-down" class="hidden size-4 text-slate-500 sm:block" />
                    </button>
                    <div class="menu-panel absolute right-0 mt-2 hidden w-52 overflow-hidden" data-dropdown-menu>
                        <a href="{{ route('profile.show') }}" class="flex items-center gap-3 px-4 py-3 text-[15px] font-semibold hover:bg-brand-50">
                            <x-icon name="profile" class="text-slate-500" /> My Profile
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-3 border-t border-slate-100 px-4 py-3 text-left text-[15px] font-semibold hover:bg-brand-50">
                                <x-icon name="logout" class="text-slate-500" /> Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
            {{ $slot }}
        </main>
    </div>

    <x-command-palette :pages="$navigation" :actions="$quickActions" />
    <x-toasts />
    <x-confirm-dialog />
</body>
</html>
