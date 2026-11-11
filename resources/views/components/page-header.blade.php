{{--
    Standard page header: serif .page-title + optional muted description
    on the left, an `actions` slot (search/filter/create buttons) right-
    aligned on the same row, and an optional `breadcrumbs` slot above it.
    Meant as a drop-in replacement for the ad-hoc per-page
    "<h1>...</h1>" + manually-placed action buttons pattern used across
    existing Index/Show views (e.g. resources/views/livewire/leads/index
    .blade.php, resources/views/livewire/leads/form.blade.php).

    <x-page-header title="Leads" description="Track incoming enquiries from first contact to conversion.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Leads' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            <x-button variant="secondary">Filter</x-button>
            <x-button href="{{ route('leads.create') }}">+ New Lead</x-button>
        </x-slot:actions>
    </x-page-header>
--}}
@props(['title', 'description' => null])

{{-- Soft-canvas pass: no divider — the title sits on the canvas and the
     rounded cards below carry the structure, as in the reference. --}}
<div {{ $attributes->merge(['class' => 'mb-4 sm:mb-6']) }}>
    @isset($breadcrumbs)
        <div class="mb-1 sm:mb-4">{{ $breadcrumbs }}</div>
    @endisset

    <div class="flex flex-wrap items-start justify-between gap-3 sm:gap-4">
        <div>
            <h1 class="page-title">{{ $title }}</h1>
            @if ($description)
                <p class="mt-1 text-[13px] text-ink-500 max-sm:line-clamp-2 sm:mt-1.5 sm:text-sm">{{ $description }}</p>
            @endif
        </div>

        @isset($actions)
            <div class="flex max-w-full shrink-0 flex-wrap items-center gap-2 sm:gap-3">
                {{ $actions }}
            </div>
        @endisset
    </div>
</div>
