{{--
    A list's filter bar. From lg up: the usual one-row panel. On phones
    and tablets only the first field (usually search) shows, beside a
    "Filters" button that unfolds the rest (app.css .filter-panel), so the
    list itself starts on the first screen. The button shows a dot while
    any filter other than the first has a value.

    <x-filter-panel>
        <div class="min-w-[220px] flex-1"><x-input label="Search" … /></div>
        <div><x-select label="Status" …>…</x-select></div>
    </x-filter-panel>
--}}
<div {{ $attributes->merge(['class' => 'filter-panel']) }}
     x-data="{ open: false, active: false,
               scan() { this.active = [...this.$el.querySelectorAll(':scope > :not(:first-child) :is(input, select)')].some((f) => f.type !== 'checkbox' && f.value !== '' && f.value !== '0' && f.value !== 'todo') } }"
     x-init="scan()" @change="scan()" @input.debounce.300ms="scan()"
     :class="open && 'is-open'">
    {{ $slot }}
    <button type="button" @click="open = ! open" class="filter-toggle btn-secondary relative !h-[42px] shrink-0 !px-3.5 lg:!hidden" :aria-expanded="open">
        {!! \App\Support\Icon::svg('sliders', 'h-4 w-4') !!}
        <span x-text="open ? 'Done' : 'Filters'">Filters</span>
        <span x-show="active && ! open" x-cloak class="absolute -right-0.5 -top-0.5 h-2.5 w-2.5 rounded-full bg-accent-500 ring-2 ring-surface"></span>
    </button>
</div>
