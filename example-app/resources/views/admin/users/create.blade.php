<x-layouts.app title="New Administrator">
    <x-page-header title="New Administrator" description="To add a teacher, use the Teachers page instead." :back="route('admin.users.index')" />

    <x-card class="max-w-3xl">
        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-6">
            @csrf
            <div class="grid gap-5 sm:grid-cols-2">
                @include('admin.users._account-fields')
            </div>
            <div class="flex gap-3">
                <button type="submit" class="btn btn-primary">Create Account</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </x-card>
</x-layouts.app>
