{{--
    Tab strip for grouping related detail-page content (premium-SaaS
    redesign spec: "Tabs for related information"). Deliberately just the
    strip, not a strip+panels wrapper — passing arbitrary panel markup
    through numbered named slots is fragile, and this app already hand-
    writes small local Alpine x-data blocks per view (see components/
    sidebar.blade.php's group-collapse state) rather than one generic
    do-everything component, so this follows the same convention. Usage:

    <div x-data="{ tab: 'overview' }">
        <x-tabs :tabs="['overview' => 'Overview', 'activity' => 'Activity']" />

        <div x-show="tab === 'overview'" x-cloak class="pt-5">...</div>
        <div x-show="tab === 'activity'" x-cloak class="pt-5">...</div>
    </div>

    $tabs is [key => label]; $active defaults to the first key and only
    needs setting if the parent's x-data starts on a different tab.
--}}
@props(['tabs' => [], 'active' => null])

@php($active = $active ?? array_key_first($tabs))

<div class="-mx-1 overflow-x-auto px-1 pb-1">
    <nav class="inline-flex gap-1 rounded-full border border-ink-200/70 bg-surface p-1" aria-label="Tabs">
        @foreach ($tabs as $key => $label)
            <button
                type="button"
                @click="tab = '{{ $key }}'"
                :class="tab === '{{ $key }}' ? 'bg-charcoal text-white dark:bg-accent-500 dark:text-charcoal' : 'text-ink-500 hover:bg-ink-50 hover:text-ink-900'"
                class="whitespace-nowrap rounded-full px-4 py-2 text-sm font-medium transition-colors"
            >
                {{ $label }}
            </button>
        @endforeach
    </nav>
</div>
