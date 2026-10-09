<header class="flex h-16 shrink-0 items-center gap-4 border-b border-gray-200 bg-white px-4 sm:px-6 lg:px-8">
    <button @click="sidebarOpen = true" class="text-gray-500 hover:text-gray-700 lg:hidden" aria-label="Open sidebar">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
        </svg>
    </button>

    <div class="flex-1"></div>

    {{-- Global search (spec §47) lands here once modules exist to search
         across; left as a visible placeholder so the layout shape is
         final now rather than re-plumbed later. --}}
    <div class="hidden w-full max-w-xs sm:block">
        <div class="relative">
            <input
                type="search"
                disabled
                placeholder="Search coming soon…"
                class="block w-full rounded-lg border-0 bg-gray-100 py-1.5 pl-3 pr-3 text-sm text-gray-500 placeholder:text-gray-400"
            >
        </div>
    </div>

    <button class="relative text-gray-400 hover:text-gray-600" aria-label="Notifications">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
        </svg>
    </button>
</header>
