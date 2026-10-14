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

<div class="border-b border-ink-200">
    <nav class="-mb-px flex gap-6 overflow-x-auto" aria-label="Tabs">
        @foreach ($tabs as $key => $label)
            <button
                type="button"
                @click="tab = '{{ $key }}'"
                :class="tab === '{{ $key }}' ? 'border-accent-600 text-accent-600' : 'border-transparent text-ink-500 hover:border-ink-300 hover:text-ink-700'"
                class="whitespace-nowrap border-b-2 px-1 py-3 text-sm font-medium transition-colors"
            >
                {{ $label }}
            </button>
        @endforeach
    </nav>
</div>
