{{-- Shared account fields for administrators and teachers. Expects $user --}}
<x-form.input name="name" label="Full name" :value="$user->name" required />
<x-form.input name="email" type="email" label="Email" :value="$user->email" required />
<x-form.input name="phone" label="Phone number" :value="$user->phone" />
<x-form.input name="staff_id" label="Staff ID" :value="$user->staff_id ?? $suggestedStaffId ?? null" inputmode="numeric" maxlength="6" placeholder="e.g. 260005"
              :hint="__('Six digits used to log in. Teachers start with the year (for example 260005); administrators start with 9 (for example 900002).')" required />
<x-form.input name="password" type="password" :label="$user->exists ? __('New password (optional)') : __('Password')" autocomplete="new-password"
              :required="! $user->exists" :hint="$user->exists ? __('Leave blank to keep the current password.') : __('At least 8 characters, with letters and numbers.')" />
<x-form.input name="password_confirmation" type="password" label="Confirm password" autocomplete="new-password" :required="! $user->exists" />
