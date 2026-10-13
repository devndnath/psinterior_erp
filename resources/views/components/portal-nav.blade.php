{{--
    Shared nav-link list for the client/vendor portal layouts. Phase 13's
    audit flagged that layouts/client-portal.blade.php and
    layouts/vendor-portal.blade.php each hardcoded their own link list
    TWICE (once for a desktop <nav>, once for a mobile <nav>) — 4
    hardcoded lists across the two files. This component holds each
    guard's link list ONCE, as a plain array (same pattern as the staff
    sidebar's $menu array in components/sidebar.blade.php), and renders a
    single <nav> that wraps and horizontally scrolls so it reads fine at
    both desktop and mobile widths — no separate mobile markup needed.

    <x-portal-nav guard="client" />
    <x-portal-nav guard="vendor" />
--}}
@props(['guard'])

@php
    $links = match ($guard) {
        'client' => [
            ['label' => 'Dashboard', 'route' => 'client.dashboard'],
            ['label' => 'Projects', 'route' => 'client.projects.index'],
            ['label' => 'Designs', 'route' => 'client.designs.index'],
            ['label' => 'Selections', 'route' => 'client.selections.index'],
            ['label' => 'Quotations', 'route' => 'client.quotations.index'],
            ['label' => 'Change Orders', 'route' => 'client.change-orders.index'],
            ['label' => 'Invoices', 'route' => 'client.invoices.index'],
            ['label' => 'Site Reports', 'route' => 'client.site-reports.index'],
            ['label' => 'Documents', 'route' => 'client.documents.index'],
            ['label' => 'Service Requests', 'route' => 'client.service-requests.index'],
        ],
        'vendor' => [
            ['label' => 'Dashboard', 'route' => 'vendor.dashboard'],
            ['label' => 'Purchase Orders', 'route' => 'vendor.purchase-orders.index'],
            ['label' => 'Ledger', 'route' => 'vendor.ledger.index'],
        ],
        default => [],
    };
@endphp

{{--
    No default display/visibility utility (flex/hidden) here on purpose —
    the caller passes those via :class so the same markup can be shown as
    a desktop row ("hidden sm:flex ...") or a mobile scroller
    ("flex sm:hidden ...") without two conflicting display classes both
    landing on this element unprefixed.
--}}
<nav {{ $attributes->merge(['class' => 'flex-wrap items-center overflow-x-auto font-medium text-gray-600']) }}>
    @foreach ($links as $link)
        <a href="{{ route($link['route']) }}" wire:navigate class="shrink-0 hover:text-brand-600">{{ $link['label'] }}</a>
    @endforeach
</nav>
