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
    $palette = [
        'gray' => 'bg-gray-100 text-gray-700',
        'blue' => 'bg-blue-100 text-blue-700',
        'green' => 'bg-green-100 text-green-700',
        'yellow' => 'bg-yellow-100 text-yellow-800',
        'red' => 'bg-red-100 text-red-700',
        'purple' => 'bg-purple-100 text-purple-700',
        'indigo' => 'bg-indigo-100 text-indigo-700',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'badge '.($palette[$color] ?? $palette['gray'])]) }}>
    {{ $label }}
</span>
