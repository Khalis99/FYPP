@props(['user'])

{{-- Activate / Deactivate an account. Deactivating asks for confirmation first. --}}
<form method="POST" action="{{ route('admin.users.toggle', $user) }}" class="inline"
      @if ($user->is_active) data-confirm="Deactivate {{ $user->name }}? They will not be able to log in until reactivated." data-confirm-button="Yes, deactivate" @endif>
    @csrf
    @method('PATCH')
    <button type="submit" class="{{ $user->is_active ? 'link-danger' : 'link' }}">{{ $user->is_active ? __('Deactivate') : __('Activate') }}</button>
</form>
