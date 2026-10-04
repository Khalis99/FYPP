@props(['name', 'label', 'value' => null, 'rows' => 4, 'hint' => null, 'required' => false])

@php
    $id = $attributes->get('id', str_replace(['[', ']'], ['_', ''], $name));
    $key = trim(str_replace(['[', ']'], ['.', ''], $name), '.');
    $hasError = $errors->has($key);
@endphp

<div>
    <label for="{{ $id }}" class="form-label">
        {{ $label }} @if ($required)<span class="text-red-600" aria-hidden="true">*</span>@endif
    </label>
    <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}" @if ($required) required @endif
              @if ($hasError) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
              {{ $attributes->except('id')->class(['form-input', 'form-input-error' => $hasError]) }}>{{ old($key, $value) }}</textarea>
    @if ($hint)
        <p class="form-hint">{{ $hint }}</p>
    @endif
    @error($key)
        <p id="{{ $id }}-error" class="form-error">{{ $message }}</p>
    @enderror
</div>
