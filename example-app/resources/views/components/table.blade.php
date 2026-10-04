{{-- Horizontally scrollable table wrapper so wide tables work on phones --}}
<div {{ $attributes->merge(['class' => 'overflow-x-auto']) }}>
    <table class="data-table">
        {{ $slot }}
    </table>
</div>
