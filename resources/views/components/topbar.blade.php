{{-- Brand-layout pass: the reference finance-dashboard header — a wide
     search field anchored left (global search is the first thing a user
     reaches for in a data-heavy ERP), utility icons and the signed-in
     user's avatar on the right. Solid surface with a hairline border so
     it reads as one plane with the light sidebar. --}}
<header class="sticky top-0 z-20 flex h-16 shrink-0 items-center gap-4 border-b border-ink-200 bg-surface px-4 sm:px-6 lg:px-8">
    <button @click="sidebarOpen = true" class="text-ink-500 hover:text-ink-700 lg:hidden" aria-label="Open sidebar">
        <x-icon name="menu" class="h-6 w-6" />
    </button>

    {{-- Phase 14: real global search across Leads/Clients/Projects/
         Invoices/Quotations (spec §47). --}}
    <div class="hidden w-full max-w-2xl sm:block">
        <livewire:global-search />
    </div>

    <div class="flex-1"></div>

    <div class="flex items-center gap-1.5">
        <x-theme-toggle />

        {{-- Phase 11: database-channel notification bell (app/Notifications/*). --}}
        <livewire:notifications.bell />

        <div class="ml-2 hidden h-9 w-9 items-center justify-center rounded-full bg-charcoal text-xs font-bold text-accent-500 dark:bg-accent-500 dark:text-charcoal sm:flex" title="{{ auth()->user()->name }}">
            {{ strtoupper(substr(auth()->user()->name ?? '?', 0, 1)) }}
        </div>
    </div>
</header>
