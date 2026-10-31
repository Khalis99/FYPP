{{-- Shared account fields for administrators and teachers. Expects $user --}}
<x-form.input name="name" label="Full name" :value="$user->name" required />
<x-form.input name="email" type="email" label="Email" :value="$user->email" required />
<x-form.input name="phone" label="Phone number" :value="$user->phone" />
<div class="hidden sm:block"></div>
<x-form.input name="password" type="password" :label="$user->exists ? __('New password (optional)') : __('Password')" autocomplete="new-password"
              :required="! $user->exists" :hint="$user->exists ? __('Leave blank to keep the current password.') : __('At least 8 characters, with letters and numbers.')" />
<x-form.input name="password_confirmation" type="password" label="Confirm password" autocomplete="new-password" :required="! $user->exists" />
