<x-layouts.app title="Profile">
    <x-page-header title="My Profile">
        <x-slot:actions>
            <a href="{{ route('profile.edit') }}" class="btn btn-primary"><x-icon name="edit" /> Edit Profile</a>
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-card class="lg:col-span-2">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-start">
                <x-avatar :user="$user" class="size-28 text-3xl" />

                <dl class="grid flex-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm text-slate-500">Full name</dt>
                        <dd class="text-[17px] font-semibold">{{ $user->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-slate-500">Email</dt>
                        <dd class="text-[17px] break-all">{{ $user->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-slate-500">Phone number</dt>
                        <dd class="text-[17px]">{{ $user->phone ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-slate-500">Role</dt>
                        <dd class="text-[17px]">{{ $user->role->label() }}</dd>
                    </div>
                    @if ($user->teacher)
                        <div>
                            <dt class="text-sm text-slate-500">Teacher ID</dt>
                            <dd class="text-[17px]">{{ $user->teacher->teacher_code }}</dd>
                        </div>
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

            @if ($user->teacher)
                <p class="mt-6 flex items-start gap-2 rounded-md bg-slate-50 p-3 text-sm text-slate-600">
                    <x-icon name="info" class="mt-0.5 size-4" />
                    Teacher ID, department and subjects are managed by the school administrator.
                </p>
            @endif
        </x-card>

        <x-card title="Change Password">
            <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
                @csrf
                @method('PUT')

                @foreach (['current_password' => 'Current password', 'password' => 'New password', 'password_confirmation' => 'Confirm new password'] as $field => $label)
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
                <button type="submit" class="btn btn-primary w-full"><x-icon name="key" /> Change Password</button>
            </form>
        </x-card>
    </div>
</x-layouts.app>
