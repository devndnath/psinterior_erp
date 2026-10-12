{{-- Phase 16: moved off border-gray-200 and text-gray-500 onto
     ink-100 and ink-500 so the topbar reads as the same product as
     the paper content below it instead of the old neutral-gray chrome. --}}
<header class="flex h-16 shrink-0 items-center gap-4 border-b border-ink-100 bg-surface px-4 sm:px-6 lg:px-8">
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

    {{-- Phase 11: database-channel notification bell (app/Notifications/*). --}}
    <livewire:notifications.bell />
</header>
