<div class="relative" x-data="{ focused: false }">
    {{-- Phase 16: moved off the old gray and brand-500 tones onto the
         ink and accent-500 scale to match the now-restyled topbar. --}}
    <div class="relative">
        <x-icon name="search" class="pointer-events-none absolute right-3.5 top-1/2 h-[18px] w-[18px] -translate-y-1/2 text-ink-400" />
        <input
            type="search"
            wire:model.live.debounce.400ms="query"
            @focus="focused = true"
            @click.outside="focused = false"
            placeholder="Search for anything — leads, clients, projects, invoices…"
            class="block w-full rounded-lg border border-ink-200 bg-surface py-2.5 pl-4 pr-10 text-sm text-ink-800 placeholder:text-ink-400 focus:border-accent-500 focus:ring-2 focus:ring-accent-500/30"
        >
    </div>

    <div
        x-show="focused && {{ strlen(trim($query)) >= 2 ? 'true' : 'false' }}"
        x-cloak
        class="absolute left-0 right-0 z-50 mt-2 max-h-96 overflow-y-auto rounded-lg border border-ink-200 bg-surface shadow-lg"
    >
        @if (empty($results))
            <p class="px-4 py-3 text-sm text-ink-500">No matches for "{{ $query }}".</p>
        @else
            @foreach ($results as $group)
                <div class="border-b border-ink-100 last:border-b-0">
                    <p class="px-4 pt-3 pb-1 text-xs font-semibold uppercase tracking-wide text-ink-400">{{ $group['label'] }}</p>
                    @foreach ($group['items'] as $item)
                        <a
                            href="{{ $item['url'] }}"
                            wire:navigate
                            class="block px-4 py-2 text-sm text-ink-700 hover:bg-ink-50"
                        >
                            <span class="font-medium">{{ $item['title'] }}</span>
                            @if ($item['subtitle'])
                                <span class="ml-2 text-ink-400">{{ $item['subtitle'] }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endforeach
        @endif
    </div>
</div>
