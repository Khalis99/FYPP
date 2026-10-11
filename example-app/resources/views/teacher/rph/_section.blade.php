{{-- One full-width RPH section (heading bar + content row). Expects $key, $content, $sections --}}
<tr><th colspan="4" class="rph-bar">{{ \Illuminate\Support\Str::upper($sections[$key][2]) }}</th></tr>
<tr>
    <td colspan="4">
        @if (is_array($content[$key]))
            @include('teacher.rph._list', ['items' => $content[$key]])
        @else
            <p class="rph-text">{{ $content[$key] !== '' ? $content[$key] : '—' }}</p>
        @endif
    </td>
</tr>
