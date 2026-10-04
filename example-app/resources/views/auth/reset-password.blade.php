<x-layouts.guest title="Reset Password">
    <div class="card p-6 sm:p-8">
        <h1 class="text-2xl font-bold text-slate-900">Choose a new password</h1>
        <p class="mt-1 text-[16px] text-slate-600">At least 8 characters, with letters and numbers.</p>

        <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-5" novalidate>
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <x-form.input name="email" type="email" label="Email address" :value="$email" required />
            <x-form.input name="password" type="password" label="New password" autocomplete="new-password" required />
            <x-form.input name="password_confirmation" type="password" label="Confirm new password" autocomplete="new-password" required />

            <button type="submit" class="btn btn-primary w-full py-3 text-base">Reset password</button>
        </form>
    </div>
</x-layouts.guest>
