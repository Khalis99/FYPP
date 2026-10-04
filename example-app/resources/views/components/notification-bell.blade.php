@props(['user'])

@php
    $unreadCount = $user->unreadNotifications()->count();
    $recent = $user->notifications()->latest()->take(6)->get();
@endphp

<div class="relative" data-dropdown>
    <button type="button" class="relative rounded-full p-2 text-slate-600 hover:bg-slate-100" data-dropdown-trigger aria-expanded="false" aria-label="Notifications ({{ $unreadCount }} unread)">
        <x-icon name="bell" class="size-6" />
        @if ($unreadCount > 0)
            <span class="absolute -top-0.5 -right-0.5 inline-flex min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-xs font-bold text-white">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
        @endif
    </button>

    <div class="absolute right-0 mt-2 hidden w-[min(22rem,calc(100vw-2rem))] overflow-hidden rounded-md border border-slate-200 bg-white shadow-lg" data-dropdown-menu>
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
            <p class="font-semibold">Notifications</p>
            @if ($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="text-sm link">Mark all as read</button>
                </form>
            @endif
        </div>

        <ul class="max-h-96 overflow-y-auto">
            @forelse ($recent as $notification)
                <li class="border-b border-slate-100 last:border-0">
                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                        @csrf
                        <button type="submit" @class(['block w-full px-4 py-3 text-left hover:bg-slate-50', 'bg-brand-50/60' => $notification->unread()])>
                            <span class="block text-[15px] font-semibold text-slate-800">{{ $notification->data['title'] ?? 'Notification' }}</span>
                            <span class="block text-sm text-slate-600">{{ $notification->data['message'] ?? '' }}</span>
                            <span class="mt-1 block text-xs text-slate-400">{{ $notification->created_at->diffForHumans() }}</span>
                        </button>
                    </form>
                </li>
            @empty
                <li class="px-4 py-6 text-center text-sm text-slate-500">You have no notifications.</li>
            @endforelse
        </ul>
    </div>
</div>
