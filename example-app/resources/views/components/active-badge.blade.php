@props(['active'])

@if ($active)
    <x-badge color="green">Active</x-badge>
@else
    <x-badge color="red">Inactive</x-badge>
@endif
