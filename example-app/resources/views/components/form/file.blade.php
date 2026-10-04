@props(['name', 'label', 'hint' => null, 'required' => false, 'current' => null, 'accept' => null])

@php
    $id = $attributes->get('id', $name);
    $hasError = $errors->has($name);
    $accept ??= collect(config('schoolhub.uploads.mimes'))->map(fn ($ext) => '.'.$ext)->implode(',');
    $hint ??= 'Allowed: '.strtoupper(implode(', ', config('schoolhub.uploads.mimes'))).'. Maximum '.(config('schoolhub.uploads.max_kb') / 1024).' MB.';
@endphp

<div>
    <label for="{{ $id }}" class="form-label">
        {{ $label }} @if ($required)<span class="text-red-600" aria-hidden="true">*</span>@endif
    </label>
    @if ($current)
        <p class="mb-2 flex items-center gap-2 text-[15px] text-slate-700">
            <x-icon name="paperclip" class="size-4 text-slate-500" /> Current file: <span class="font-medium">{{ $current }}</span>
        </p>
    @endif
    <input type="file" id="{{ $id }}" name="{{ $name }}" accept="{{ $accept }}" @if ($required) required @endif
           {{ $attributes->except('id')->class([
               'block w-full rounded-md border border-slate-300 bg-white text-[15px] text-slate-700 file:mr-4 file:cursor-pointer file:border-0 file:bg-brand-50 file:px-4 file:py-2.5 file:font-semibold file:text-brand-700 hover:file:bg-brand-100',
               'border-red-500' => $hasError,
           ]) }}>
    <p class="form-hint">{{ $hint }}</p>
    @error($name)
        <p class="form-error">{{ $message }}</p>
    @enderror
</div>
