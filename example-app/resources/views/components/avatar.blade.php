@props(['user'])

@if ($user->profile_photo_url)
    <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" {{ $attributes->merge(['class' => 'shrink-0 rounded-full object-cover']) }}>
@else
    <span {{ $attributes->merge(['class' => 'inline-flex shrink-0 items-center justify-center rounded-full bg-brand-600 font-semibold text-white']) }} aria-hidden="true">
        {{ $user->initials }}
    </span>
@endif
