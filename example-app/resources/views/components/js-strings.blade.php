{{-- Text that app.js needs in the current language --}}
<script>
    window.pelitaText = {!! json_encode([
        'showPassword' => __('Show password'),
        'hidePassword' => __('Hide password'),
    ], JSON_UNESCAPED_UNICODE) !!};
</script>
