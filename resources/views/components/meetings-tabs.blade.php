{{--
    Meetings | Calls switch at the top of both pages of the Meetings
    section, so calls sit right next to meetings. Each tab shows only when
    the user can open that page.
--}}
@props(['active' => 'meetings'])

@php
    $user = auth()->user();
    $tabs = array_filter([
        'meetings' => $user?->can('view_meetings') ? ['Meetings', route('meetings.index'), 'calendar'] : null,
        'calls' => ($user?->can('view_leads') || $user?->can('view_clients')) ? ['Calls', route('calls.index'), 'phone'] : null,
    ]);
@endphp

@if (count($tabs) > 1)
    <nav class="-mt-2 mb-5 inline-flex gap-1 rounded-full border border-ink-200/70 bg-surface p-1" aria-label="Meetings section">
        @foreach ($tabs as $key => [$label, $href, $icon])
            <a href="{{ $href }}" wire:navigate @class([
                'inline-flex items-center gap-2 whitespace-nowrap rounded-full px-4 py-2 text-sm font-medium transition-colors',
                'bg-charcoal text-white dark:bg-accent-500 dark:text-charcoal' => $active === $key,
                'text-ink-500 hover:bg-ink-50 hover:text-ink-900' => $active !== $key,
            ])>
                <x-icon :name="$icon" class="h-4 w-4" />{{ $label }}
            </a>
        @endforeach
    </nav>
@endif
