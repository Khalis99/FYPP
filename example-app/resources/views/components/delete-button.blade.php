@props(['action', 'message' => __('Are you sure you want to delete this? This cannot be undone.'), 'label' => __('Delete'), 'button' => 'link-danger'])

<form method="POST" action="{{ $action }}" class="inline" data-confirm="{{ $message }}">
    @csrf
    @method('DELETE')
    <button type="submit" class="{{ $button }}">{{ $label }}</button>
</form>
