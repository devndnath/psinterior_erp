{{-- Minimalist redesign pass: sticky + a soft backdrop-blur so the bar
     stays legible over whatever scrolls beneath it without resorting to a
     heavy shadow, and a thin divider ahead of the notification bell so
     the two right-hand controls don't visually run together. --}}
<header class="sticky top-0 z-20 flex h-16 shrink-0 items-center gap-4 border-b border-ink-100 bg-surface/90 px-4 backdrop-blur-sm sm:px-6 lg:px-8">
    <button @click="sidebarOpen = true" class="text-ink-500 hover:text-ink-700 lg:hidden" aria-label="Open sidebar">
        <x-icon name="menu" class="h-6 w-6" />
    </button>

    <div class="flex-1"></div>

    {{-- Phase 14: real global search across Leads/Clients/Projects/
         Invoices/Quotations (spec §47), replacing the disabled
         placeholder input. --}}
    <div class="hidden w-full max-w-xs sm:block">
        <livewire:global-search />
    </div>

    <div class="hidden h-6 w-px bg-ink-100 sm:block"></div>

    {{-- Phase 11: database-channel notification bell (app/Notifications/*). --}}
    <livewire:notifications.bell />
</header>
