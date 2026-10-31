<x-layouts.app title="Users">
    <x-page-header title="Users" description="All accounts that can log in to Pelita.">
        <x-slot:actions>
            <a href="{{ route('admin.teachers.create') }}" class="btn btn-secondary"><x-icon name="plus" /> New Teacher</a>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary"><x-icon name="plus" /> New Administrator</a>
        </x-slot:actions>
    </x-page-header>

    <form method="GET" class="card mb-6 grid gap-4 p-4 sm:grid-cols-2 lg:grid-cols-4 lg:items-end">
        <x-form.input name="search" label="Search name or email" :value="$filters['search'] ?? ''" />
        <x-form.select name="role" label="Role" :options="$roleOptions" :value="$filters['role'] ?? ''" placeholder="All roles" />
        <x-form.select name="status" label="Status" :options="['active' => 'Active', 'inactive' => 'Inactive']" :value="$filters['status'] ?? ''" placeholder="All" />
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary"><x-icon name="search" /> Search</button>
            @if (array_filter($filters))
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Clear</a>
            @endif
        </div>
    </form>

    <x-card :padding="false">
        <x-table>
            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Created</th><th class="text-right">Actions</th></tr></thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td class="font-medium">
                            <span class="flex items-center gap-3"><x-avatar :user="$user" class="size-8 text-xs" /> {{ $user->name }}</span>
                        </td>
                        <td class="break-all">{{ $user->email }}</td>
                        <td><x-badge :color="$user->isAdmin() ? 'purple' : 'blue'">{{ $user->role->label() }}</x-badge></td>
                        <td><x-active-badge :active="$user->is_active" /></td>
                        <td class="whitespace-nowrap">{{ $user->created_at->format('j M Y') }}</td>
                        <td class="text-right whitespace-nowrap">
                            <div class="inline-flex gap-3">
                                <a href="{{ route('admin.users.edit', $user) }}" class="link">Edit</a>
                                @unless ($user->is(auth()->user()))
                                    <x-status-toggle :user="$user" />
                                @endunless
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-slate-500">No users found.</td></tr>
                @endforelse
            </tbody>
        </x-table>
        <div class="border-t border-slate-100 p-4">{{ $users->links() }}</div>
    </x-card>
</x-layouts.app>
