{{--
    Blade-component wrapper around the existing .btn-primary/.btn-secondary
    /.btn-danger CSS classes (app.css) — same visual style, just reusable
    as a component with a slot instead of hand-typing the class each time.

    <x-button type="submit">Save changes</x-button>
    <x-button variant="secondary" href="{{ route('leads.index') }}">Cancel</x-button>
    <x-button variant="danger" wire:click="delete">Delete</x-button>
--}}
@props(['variant' => 'primary', 'type' => 'button'])

@php
    $class = match ($variant) {
        'secondary' => 'btn-secondary',
        'danger' => 'btn-danger',
        default => 'btn-primary',
    };
@endphp

@if ($attributes->has('href'))
    <a {{ $attributes->merge(['class' => $class]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $class]) }}>{{ $slot }}</button>
@endif
