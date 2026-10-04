{{-- One RPH section. Expects $key, $content, $sections --}}
<div class="rph-section">
    <h2>{{ $sections[$key][0] }}</h2>
    @if (is_array($content[$key]))
        @if ($content[$key])
            <ol>@foreach ($content[$key] as $item)<li>{{ $item }}</li>@endforeach</ol>
        @else
            <p>—</p>
        @endif
    @else
        <p>{{ $content[$key] !== '' ? $content[$key] : '—' }}</p>
    @endif
</div>
