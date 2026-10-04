@props(['name', 'label', 'options' => [], 'value' => null, 'placeholder' => null, 'hint' => null, 'required' => false])

{{-- $options is an array of value => label --}}
@php
    $id = $attributes->get('id', str_replace(['[', ']'], ['_', ''], $name));
    $key = trim(str_replace(['[', ']'], ['.', ''], $name), '.');
    $selected = (string) old($key, $value instanceof \BackedEnum ? $value->value : $value);
    $hasError = $errors->has($key);
@endphp

<div>
    <label for="{{ $id }}" class="form-label">
        {{ $label }} @if ($required)<span class="text-red-600" aria-hidden="true">*</span>@endif
    </label>
    <select id="{{ $id }}" name="{{ $name }}" @if ($required) required @endif
            @if ($hasError) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
            {{ $attributes->except('id')->class(['form-input', 'form-input-error' => $hasError]) }}>
        @if ($placeholder !== null)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected($selected === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>
    @if ($hint)
        <p class="form-hint">{{ $hint }}</p>
    @endif
    @error($key)
        <p id="{{ $id }}-error" class="form-error">{{ $message }}</p>
    @enderror
</div>
