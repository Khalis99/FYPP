@props(['title' => null])

@php
    $user = auth()->user();

    $navigation = $user->isAdmin()
        ? [
            ['route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'icon' => 'dashboard', 'label' => 'Dashboard'],
            ['route' => 'admin.users.index', 'active' => 'admin.users.*', 'icon' => 'users', 'label' => 'Users'],
            ['route' => 'admin.teachers.index', 'active' => 'admin.teachers.*', 'icon' => 'teachers', 'label' => 'Teachers'],
            ['route' => 'admin.students.index', 'active' => 'admin.students.*', 'icon' => 'students', 'label' => 'Students'],
            ['route' => 'admin.classes.index', 'active' => ['admin.classes.*', 'admin.subjects.*'], 'icon' => 'classes', 'label' => 'Classes'],
            ['route' => 'admin.announcements.index', 'active' => 'admin.announcements.*', 'icon' => 'announcements', 'label' => 'Announcements'],
            ['route' => 'admin.timetable.index', 'active' => 'admin.timetable.*', 'icon' => 'timetable', 'label' => 'Timetable'],
            ['route' => 'profile.show', 'active' => 'profile.*', 'icon' => 'profile', 'label' => 'Profile'],
        ]
        : [
            ['route' => 'teacher.dashboard', 'active' => 'teacher.dashboard', 'icon' => 'dashboard', 'label' => 'Dashboard'],
            ['route' => 'teacher.classes.index', 'active' => 'teacher.classes.*', 'icon' => 'classes', 'label' => 'My Classes'],
            ['route' => 'teacher.materials.index', 'active' => 'teacher.materials.*', 'icon' => 'materials', 'label' => 'Teaching Materials'],
            ['route' => 'teacher.assignments.index', 'active' => ['teacher.assignments.*', 'teacher.submissions.*'], 'icon' => 'assignments', 'label' => 'Assignments'],
            ['route' => 'teacher.students.index', 'active' => 'teacher.students.*', 'icon' => 'students', 'label' => 'Student Records'],
            ['route' => 'teacher.timetable.index', 'active' => 'teacher.timetable.*', 'icon' => 'timetable', 'label' => 'Timetable'],
            ['route' => 'teacher.announcements.index', 'active' => 'teacher.announcements.*', 'icon' => 'announcements', 'label' => 'Announcements'],
            ['route' => 'teacher.rph.create', 'active' => ['teacher.rph.create', 'teacher.rph.generate', 'teacher.rph.draft'], 'icon' => 'sparkles', 'label' => 'RPH Generator'],
            ['route' => 'teacher.rph.index', 'active' => ['teacher.rph.index', 'teacher.rph.show', 'teacher.rph.edit'], 'icon' => 'document', 'label' => 'My RPH'],
            ['route' => 'profile.show', 'active' => 'profile.*', 'icon' => 'profile', 'label' => 'Profile'],
        ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title.' · ' : '' }}SchoolHub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 font-sans text-base text-slate-900 antialiased">
    {{-- Mobile backdrop --}}
    <div id="sidebar-backdrop" class="fixed inset-0 z-30 hidden bg-slate-900/40 lg:hidden" data-sidebar-toggle></div>

    {{-- Sidebar --}}
    <aside id="sidebar" class="no-print fixed inset-y-0 left-0 z-40 flex w-68 -translate-x-full flex-col border-r border-slate-200 bg-white transition-transform lg:translate-x-0">
        <a href="{{ route($user->role->homeRoute()) }}" class="flex items-center gap-3 bg-brand-600 px-5 py-4 text-white">
            <x-icon name="classes" class="size-8" />
            <span class="leading-tight">
                <span class="block text-lg font-bold tracking-wide">SchoolHub</span>
                <span class="block text-xs font-medium text-brand-100">{{ $user->isAdmin() ? 'Admin Portal' : 'Teacher Portal' }}</span>
            </span>
        </a>

        <nav class="flex-1 overflow-y-auto py-3" aria-label="Main navigation">
            @foreach ($navigation as $item)
                <x-sidebar-link :href="route($item['route'])" :active="request()->routeIs(...(array) $item['active'])" :icon="$item['icon']">
                    {{ $item['label'] }}
                </x-sidebar-link>
            @endforeach
        </nav>

        <form method="POST" action="{{ route('logout') }}" class="border-t border-slate-200 p-3">
            @csrf
            <button type="submit" class="flex w-full items-center gap-4 rounded-md px-4 py-3 text-left text-[16px] text-slate-700 hover:bg-slate-100">
                <x-icon name="logout" class="size-6 text-slate-500" />
                Logout
            </button>
        </form>
    </aside>

    <div class="lg:pl-68">
        {{-- Top bar --}}
        <header class="no-print sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-slate-200 bg-white px-4 sm:px-6">
            <button type="button" class="rounded-md p-2 text-slate-600 hover:bg-slate-100 lg:hidden" data-sidebar-toggle aria-label="Open menu">
                <x-icon name="menu" class="size-6" />
            </button>

            <h1 class="truncate text-lg font-semibold text-slate-800">{{ $title }}</h1>

            <div class="ml-auto flex items-center gap-2 sm:gap-4">
                <x-notification-bell :user="$user" />

                <div class="relative" data-dropdown>
                    <button type="button" class="flex items-center gap-2 rounded-full py-1 pr-2 pl-1 hover:bg-slate-100" data-dropdown-trigger aria-expanded="false" aria-haspopup="true">
                        <x-avatar :user="$user" class="size-9 text-sm" />
                        <span class="hidden text-left leading-tight sm:block">
                            <span class="block text-sm font-semibold text-slate-800">{{ $user->name }}</span>
                            <span class="block text-xs text-slate-500">{{ $user->role->label() }}</span>
                        </span>
                        <x-icon name="chevron-down" class="hidden size-4 text-slate-500 sm:block" />
                    </button>
                    <div class="absolute right-0 mt-2 hidden w-52 overflow-hidden rounded-md border border-slate-200 bg-white shadow-lg" data-dropdown-menu>
                        <a href="{{ route('profile.show') }}" class="flex items-center gap-3 px-4 py-3 text-[15px] hover:bg-slate-50">
                            <x-icon name="profile" class="text-slate-500" /> My Profile
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-3 border-t border-slate-100 px-4 py-3 text-left text-[15px] hover:bg-slate-50">
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

    <x-toasts />
    <x-confirm-dialog />
</body>
</html>
