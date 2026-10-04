<x-layouts.app title="Edit Profile">
    <x-page-header title="Edit Profile" :back="route('profile.show')" />

    <x-card class="max-w-3xl">
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="flex items-center gap-5">
                <x-avatar :user="$user" class="size-20 text-2xl" />
                <div class="flex-1">
                    <x-form.file name="photo" label="Profile photo" accept=".jpg,.jpeg,.png" hint="JPG or PNG, maximum 2 MB." />
                    @if ($user->profile_photo_path)
                        <label class="mt-2 flex items-center gap-2 text-[15px] text-slate-700">
                            <input type="checkbox" name="remove_photo" value="1" class="size-5 rounded border-slate-300"> Remove current photo
                        </label>
                    @endif
                </div>
            </div>

            <x-form.input name="name" label="Full name" :value="$user->name" required />
            <x-form.input name="email" type="email" label="Email" :value="$user->email" required />
            <x-form.input name="phone" label="Phone number" :value="$user->phone" hint="Example: 012-3456789" />

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('profile.show') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </x-card>
</x-layouts.app>
