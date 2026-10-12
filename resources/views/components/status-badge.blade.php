{{--
    Generic status pill. Every module's status enum maps its cases to a
    color here via the $color prop rather than each view inventing its own
    badge markup — keeps 80+ status values visually consistent app-wide
    (spec §57: "status badges... consistent colors").

    <x-status-badge label="Approved" color="green" />
    color: gray | blue | green | yellow | red | purple | indigo
--}}
@props(['label', 'color' => 'gray'])

@php
    // Phase 16: the color *names* each status enum's color() method
    // passes in (gray, blue, green, yellow, red, purple, and indigo — see
    // e.g. LeadStatus::color(), InvoiceStatus::color()) are untouched, so
    // no enum needs editing. Only what each name renders as moved off the
    // pre-Phase-13 default Tailwind swatches onto the ink, accent, success,
    // warning, and danger design-system scale, matching the panels and
    // tables around every badge. purple and indigo have no design-system
    // equivalent (rare, decorative statuses) and stay on Tailwind's defaults.
    $palette = [
        'gray' => 'bg-ink-100 text-ink-700',
        'blue' => 'bg-accent-100 text-accent-700',
        'green' => 'bg-success-100 text-success-700',
        'yellow' => 'bg-warning-100 text-warning-800',
        'red' => 'bg-danger-100 text-danger-700',
        'purple' => 'bg-purple-100 text-purple-700',
        'indigo' => 'bg-indigo-100 text-indigo-700',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'badge '.($palette[$color] ?? $palette['gray'])]) }}>
    {{ $label }}
</span>
