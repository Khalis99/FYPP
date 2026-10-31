@props(['href', 'icon', 'active' => false])

<a href="{{ $href }}" class="nav-link" @if ($active) aria-current="page" @endif>
    <x-icon :name="$icon" class="nav-icon" />
    <span>{{ $slot }}</span>
</a>
