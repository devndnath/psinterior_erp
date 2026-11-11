<div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
    <button type="button" class="icon-btn" aria-label="My schedule" title="My schedule"
            @click="open = ! open; if (open && ! $wire.loaded) $wire.load()" :aria-expanded="open">
        {!! \App\Support\Icon::svg('calendar', 'h-5 w-5') !!}
    </button>

    {{-- Phones: dim the page behind the sheet. --}}
    <div x-show="open" x-cloak x-transition.opacity @click="open = false" class="fixed inset-0 z-40 bg-black/40 sm:hidden"></div>

    <div x-show="open" x-cloak @click.outside="open = false"
         x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         class="fixed inset-x-0 bottom-0 z-50 flex max-h-[85vh] flex-col overflow-hidden rounded-t-[28px] border border-ink-200/70 bg-surface pb-[env(safe-area-inset-bottom)] shadow-2xl
                sm:absolute sm:inset-x-auto sm:bottom-auto sm:right-0 sm:mt-2 sm:max-h-[70vh] sm:w-[400px] sm:rounded-3xl sm:pb-0 dark:border-white/[0.08]">
        <div class="mx-auto mt-2 h-1.5 w-10 shrink-0 rounded-full bg-ink-200 sm:hidden"></div>

        <div class="flex shrink-0 items-center justify-between gap-3 px-5 pb-3 pt-3 sm:pt-4">
            <div>
                <p class="section-title">My schedule</p>
                <p class="section-sub">{{ $today->format('l, d M') }}</p>
            </div>
            <div class="inline-flex rounded-full border border-ink-200/70 p-0.5 text-xs font-semibold">
                @foreach ([7 => '7 days', 30 => '30 days'] as $n => $label)
                    <button type="button" wire:click="setDays({{ $n }})" @class([
                        'rounded-full px-3 py-1.5',
                        'bg-charcoal text-white dark:bg-accent-500 dark:text-charcoal' => $days === $n,
                        'text-ink-500' => $days !== $n,
                    ])>{{ $label }}</button>
                @endforeach
            </div>
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain border-t border-ink-100 px-3 py-2">
            @if (! $loaded)
                <p class="px-2 py-8 text-center text-sm text-ink-400" wire:loading.remove>Loading…</p>
            @elseif ($groups->isEmpty())
                <div class="px-2 py-10 text-center">
                    <p class="text-sm font-semibold text-ink-700">Nothing scheduled</p>
                    <p class="mt-1 text-xs text-ink-500">No meetings, visits, calls or deadlines in the next {{ $days }} days.</p>
                </div>
            @else
                @foreach ($groups as $date => $events)
                    @php($d = \Carbon\CarbonImmutable::parse($date))
                    <p class="sticky top-0 z-10 bg-surface/95 px-2 pb-1 pt-3 text-xs font-semibold text-ink-500 backdrop-blur">
                        {{ $d->isSameDay($today) ? 'Today' : ($d->isSameDay($today->addDay()) ? 'Tomorrow' : $d->format('l')) }}
                        <span class="font-normal text-ink-400">· {{ $d->format('d M') }} · {{ $events->count() }}</span>
                    </p>
                    @foreach ($events as $e)
                        @php($dot = ['purple' => 'bg-purple-500', 'blue' => 'bg-sky-500', 'indigo' => 'bg-indigo-500', 'green' => 'bg-success-500', 'teal' => 'bg-teal-500', 'red' => 'bg-danger-500', 'yellow' => 'bg-accent-500', 'gray' => 'bg-ink-400'][$e['color']] ?? 'bg-ink-400')
                        <a @if ($e['url']) href="{{ $e['url'] }}" wire:navigate @click="open = false" @endif
                           class="flex items-start gap-3 rounded-2xl px-2 py-2.5 hover:bg-ink-50 active:bg-ink-100">
                            <span class="w-11 shrink-0 pt-0.5 text-right text-xs font-semibold tabular-nums text-ink-700">{{ $e['time'] ?? '' }}</span>
                            <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full {{ $dot }}"></span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-medium text-ink-900">{{ $e['title'] }}</span>
                                <span class="block truncate text-xs text-ink-500">{{ $e['type'] }}@if ($e['subtitle']) · {{ $e['subtitle'] }}@endif</span>
                            </span>
                        </a>
                    @endforeach
                @endforeach
            @endif
        </div>

        <a href="{{ route('calendar.index') }}" wire:navigate @click="open = false"
           class="flex shrink-0 items-center justify-center gap-2 border-t border-ink-100 px-5 py-3.5 text-sm font-semibold text-accent-700 hover:bg-ink-50 dark:text-accent-400">
            Open full calendar {!! \App\Support\Icon::svg('chevron-right', 'h-4 w-4') !!}
        </a>
    </div>
</div>
