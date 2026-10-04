<x-layouts.guest title="Login">
    <div class="card p-6 sm:p-8">
        <h1 class="text-2xl font-bold text-slate-900">Log in to SchoolHub</h1>
        <p class="mt-1 text-[16px] text-slate-600">Use the email and password given by your school administrator.</p>

        <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-5" novalidate>
            @csrf

            <x-form.input name="email" type="email" label="Email address" autocomplete="username" autofocus required />
            <x-form.input name="password" type="password" label="Password" autocomplete="current-password" required />

            <div class="flex flex-wrap items-center justify-between gap-3">
                <label class="flex items-center gap-2 text-[15px] text-slate-700">
                    <input type="checkbox" name="remember" value="1" class="size-5 rounded border-slate-300 text-brand-600" @checked(old('remember'))>
                    Remember me
                </label>
                <a href="{{ route('password.request') }}" class="text-[15px] link">Forgot password?</a>
            </div>

            <button type="submit" class="btn btn-primary w-full py-3 text-base">Log in</button>
        </form>
    </div>
</x-layouts.guest>
