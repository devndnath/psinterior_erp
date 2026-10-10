<div class="relative" x-data="{ open: false }" @click.outside="open = false">
    <button @click="open = ! open" type="button" class="relative text-gray-400 hover:text-gray-600" aria-label="Notifications">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
        </svg>
        @if ($unreadCount > 0)
            <span class="absolute -right-1 -top-1 flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-semibold text-white">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    <div
        x-show="open" x-cloak x-transition
        class="absolute right-0 z-50 mt-2 w-80 rounded-lg border border-gray-200 bg-white shadow-lg"
    >
        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
            <span class="text-sm font-semibold text-gray-900">Notifications</span>
            @if ($unreadCount > 0)
                <button type="button" wire:click="markAllRead" class="text-xs font-medium text-brand-600 hover:underline">Mark all read</button>
            @endif
        </div>

        <div class="max-h-96 overflow-y-auto">
            @forelse ($recent as $notification)
                <button
                    type="button"
                    wire:click="markAsReadAndGo('{{ $notification->id }}', '{{ $notification->data['url'] ?? '#' }}')"
                    class="flex w-full items-start gap-2 border-b border-gray-50 px-4 py-3 text-left text-sm hover:bg-gray-50"
                >
                    @if (! $notification->read_at)
                        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-brand-600"></span>
                    @else
                        <span class="mt-1.5 h-2 w-2 shrink-0"></span>
                    @endif
                    <span class="flex-1">
                        <span class="block text-gray-900">{{ $notification->data['message'] ?? 'Notification' }}</span>
                        <span class="block text-xs text-gray-400">{{ $notification->created_at->diffForHumans() }}</span>
                    </span>
                </button>
            @empty
                <p class="px-4 py-6 text-center text-sm text-gray-400">No notifications yet.</p>
            @endforelse
        </div>
    </div>
</div>
