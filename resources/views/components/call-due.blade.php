{{--
    When a call is due, as a chip: "3 days late" (red), "Today" (gold),
    "Tomorrow" / a date (neutral) — or, for a finished call, its outcome
    or status. Shared by every call list so they read the same.
--}}
@props(['call'])

@php
    $today = today();
    $pending = $call->status === \App\Enums\FollowupStatus::Pending;
    [$label, $tone] = match (true) {
        ! $pending && $call->outcome !== null => [$call->outcome->label(), $call->outcome->color()],
        ! $pending => [$call->status->label(), $call->status->color()],
        $call->date->lt($today) => [$call->date->diffForHumans($today, ['syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE]).' late', 'red'],
        $call->date->isSameDay($today) => ['Today'.($call->time ? ' · '.\Illuminate\Support\Str::substr($call->time, 0, 5) : ''), 'gold'],
        $call->date->isSameDay($today->copy()->addDay()) => ['Tomorrow', 'gray'],
        default => [$call->date->format('d M'), 'gray'],
    };
    $classes = [
        'red' => 'bg-danger-50 text-danger-700 dark:bg-danger-500/10 dark:text-danger-300',
        'gold' => 'bg-accent-500/15 text-accent-700 dark:text-accent-400',
        'green' => 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400',
        'yellow' => 'bg-warning-50 text-warning-800 dark:bg-warning-500/10 dark:text-warning-300',
        'blue' => 'bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-300',
        'gray' => 'bg-ink-100 text-ink-700',
    ][$tone] ?? 'bg-ink-100 text-ink-700';
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex shrink-0 items-center whitespace-nowrap rounded-full px-2.5 py-1 text-[11px] font-semibold '.$classes]) }}>{{ $label }}</span>
