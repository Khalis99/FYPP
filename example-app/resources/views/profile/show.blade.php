<x-layouts.app title="Profile">
    <x-page-header title="My Profile">
        <x-slot:actions>
            <a href="{{ route('profile.edit') }}" class="btn btn-primary"><x-icon name="edit" /> Edit Profile</a>
        </x-slot:actions>
    </x-page-header>

    <div class="grid items-start gap-6 lg:grid-cols-3">
        <x-card class="lg:col-span-2">
            <div>
                <div class="flex flex-col items-center gap-5 border-b border-slate-200 pb-6 text-center sm:flex-row sm:text-left">
                    <x-avatar :user="$user" class="size-24 text-3xl ring-4 ring-brand-100" />

                    <div>
                        <p class="text-2xl font-extrabold text-slate-900">{{ $user->name }}</p>
                        <p class="mt-2 flex flex-wrap items-center justify-center gap-x-3 gap-y-1 sm:justify-start">
                            <x-badge :color="$user->isAdmin() ? 'purple' : 'blue'">{{ $user->role->label() }}</x-badge>
                            <span class="text-[15px] text-slate-600">Staff ID <span class="font-bold tracking-wider text-slate-900">{{ $user->staff_id }}</span></span>
                        </p>
                    </div>
                </div>

                <dl class="grid gap-x-8 gap-y-6 pt-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm text-slate-500">Email</dt>
                        <dd class="text-[17px] break-all">{{ $user->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-slate-500">Phone number</dt>
                        <dd class="text-[17px]">{{ $user->phone ?: '—' }}</dd>
                    </div>
                    @if ($user->teacher)
                        <div>
                            <dt class="text-sm text-slate-500">Department</dt>
                            <dd class="text-[17px]">{{ $user->teacher->department ?: '—' }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-sm text-slate-500">Subjects taught</dt>
                            <dd class="mt-1 flex flex-wrap gap-2">
                                @forelse ($subjects as $subject)
                                    <x-badge color="blue">{{ $subject->name }}</x-badge>
                                @empty
                                    <span class="text-slate-600">No subjects assigned yet.</span>
                                @endforelse
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>
        </x-card>

        <x-card title="Change Password">
            <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
                @csrf
                @method('PUT')

                @foreach (['current_password' => __('Current password'), 'password' => __('New password'), 'password_confirmation' => __('Confirm new password')] as $field => $label)
                    <div>
                        <label for="{{ $field }}" class="form-label">{{ $label }}</label>
                        <input type="password" id="{{ $field }}" name="{{ $field }}" autocomplete="{{ $field === 'current_password' ? 'current-password' : 'new-password' }}"
                               @class(['form-input', 'form-input-error' => $errors->password->has($field)]) required>
                        @if ($errors->password->has($field))
                            <p class="form-error">{{ $errors->password->first($field) }}</p>
                        @endif
                    </div>
                @endforeach

                <p class="form-hint">At least 8 characters, with letters and numbers.</p>
                <button type="submit" class="btn btn-primary w-full">Change Password</button>
            </form>
        </x-card>
    </div>
</x-layouts.app>
