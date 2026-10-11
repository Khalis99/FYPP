{{-- Numbered list, or a dash when empty. Expects $items --}}
@if ($items)
    <ol>@foreach ($items as $item)<li>{{ $item }}</li>@endforeach</ol>
@else
    <p>—</p>
@endif
