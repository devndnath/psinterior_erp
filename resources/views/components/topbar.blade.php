{{-- Soft-canvas pass: the header sits directly on the page canvas (no bar,
     no border), like the reference — brand mark in a round chip lined up
     over the sidebar rail with the company name beside it, a round search
     field, then the notification bell and the signed-in user's avatar
     menu (which holds sign-out, now that the rail's footer carries the
     appearance toggle). --}}
@php
    $companyName = \App\Models\Setting::get('company.name', config('app.name'));
    $user = auth()->user();
@endphp
{{-- Phones/tablets: a compact app bar (the menu lives in the bottom tab
     bar, <x-bottom-nav>), with search opening as a full-width sheet. --}}
<header class="app-topbar flex h-[60px] shrink-0 items-center gap-2 px-3 sm:gap-3 lg:h-[76px] lg:px-5" x-data="{ searchOpen: false }">

    <a href="{{ route('dashboard') }}" wire:navigate class="flex min-w-0 items-center gap-3">
        <span class="flex w-[72px] shrink-0 justify-center max-lg:w-auto">
            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-charcoal text-xs font-extrabold lg:h-12 lg:w-12 lg:text-sm tracking-tight text-accent-500 ring-4 ring-surface dark:bg-accent-500 dark:text-charcoal dark:ring-white/5">
                {{ strtoupper(substr($companyName, 0, 2)) }}
            </span>
        </span>
        <span class="hidden min-w-0 sm:block md:hidden lg:block">
            <span class="block truncate text-lg font-semibold leading-tight tracking-tight text-ink-900">{{ $companyName }}</span>
            <span class="block truncate text-xs font-medium text-ink-500">Interior &amp; Architecture ERP</span>
        </span>
    </a>

    <div class="flex-1"></div>

    {{-- Phase 14: real global search across Leads/Clients/Projects/
         Invoices/Quotations (spec §47). --}}
    <div class="w-full max-w-md max-md:hidden"
         :class="searchOpen && '!fixed inset-x-0 top-0 z-50 !block max-w-none bg-paper p-3 pt-[max(0.75rem,env(safe-area-inset-top))] shadow-lg'"
         @keydown.escape.window="searchOpen = false" @click.outside="searchOpen = false">
        <div class="flex items-center gap-2">
            <div class="min-w-0 flex-1"><livewire:global-search /></div>
            <button type="button" x-show="searchOpen" x-cloak @click="searchOpen = false" class="text-sm font-semibold text-ink-600 md:hidden">Cancel</button>
        </div>
    </div>

    <div class="flex items-center gap-2">
        <button type="button" class="icon-btn md:hidden" aria-label="Search"
                @click.stop="searchOpen = true; $nextTick(() => $root.querySelector('input')?.focus())">
            <x-icon name="search" class="h-5 w-5" />
        </button>
        {{-- Phase 11: database-channel notification bell (app/Notifications/*). --}}
        <livewire:schedule.popup />
        <livewire:notifications.bell />

        <div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
            <button type="button" @click="open = ! open" class="flex items-center gap-2 rounded-full border border-ink-200/80 bg-surface p-1 pr-1 transition hover:border-ink-300 sm:pr-3" aria-haspopup="true" :aria-expanded="open">
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-accent-500 text-xs font-bold text-charcoal">
                    {{ strtoupper(substr($user->name ?? '?', 0, 1)) }}
                </span>
                <span class="hidden max-w-[9rem] truncate text-sm font-semibold text-ink-800 sm:block">{{ \Illuminate\Support\Str::of($user->name)->before(' ') }}</span>
                <x-icon name="chevron-down" class="hidden h-4 w-4 text-ink-400 sm:block" />
            </button>
            <div x-show="open" x-cloak x-transition.origin.top.right
                class="absolute right-0 z-50 mt-2 w-60 rounded-3xl border border-ink-200/70 bg-surface p-2 shadow-xl shadow-black/[0.06]">
                <div class="px-3 pb-2 pt-1.5">
                    <p class="truncate text-sm font-semibold text-ink-900">{{ $user->name }}</p>
                    <p class="truncate text-xs text-ink-500">{{ $user->getRoleNames()->first() ?? 'User' }} · {{ $user->email }}</p>
                </div>
                <div class="my-1 h-px bg-ink-100"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item-danger flex items-center gap-2">
                        <x-icon name="log-out" class="h-4 w-4" /> Log out
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
