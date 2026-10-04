<x-layouts.app title="Edit User">
    <x-page-header :title="'Edit '.$user->name" :back="route('admin.users.index')" />

    <x-card class="max-w-3xl">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6">
            @csrf
            @method('PUT')
            <div class="grid gap-5 sm:grid-cols-2">
                @include('admin.users._account-fields')
            </div>
            <div class="flex gap-3">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </x-card>
</x-layouts.app>
