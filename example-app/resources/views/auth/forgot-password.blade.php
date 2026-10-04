<x-layouts.guest title="Forgot Password">
    <div class="card p-6 sm:p-8">
        <h1 class="text-2xl font-bold text-slate-900">Forgot your password?</h1>
        <p class="mt-1 text-[16px] text-slate-600">Enter your email address and we will send you a link to choose a new password.</p>

        <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-5" novalidate>
            @csrf
            <x-form.input name="email" type="email" label="Email address" autofocus required />
            <button type="submit" class="btn btn-primary w-full py-3 text-base">Send reset link</button>
        </form>

        <a href="{{ route('login') }}" class="mt-6 inline-flex items-center gap-1.5 text-[15px] link">
            <x-icon name="back" class="size-4" /> Back to login
        </a>
    </div>
</x-layouts.guest>
