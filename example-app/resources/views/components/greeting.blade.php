@props(['user'])

@php
    $hour = (int) now()->format('G');
    $phrase = match (true) {
        $hour < 12 => __('Good morning'),
        $hour < 18 => __('Good afternoon'),
        default => __('Good evening'),
    };

    // "Nur Aisyah binti Rahman" -> "Cikgu Nur Aisyah"; admins keep their own title and name.
    $name = $user->isAdmin()
        ? $user->name
        : 'Cikgu '.\Illuminate\Support\Str::of($user->name)->before(' binti ')->before(' bin ');
@endphp

{{ $phrase }}, {{ $name }}