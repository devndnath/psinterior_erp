{{--
    Progress ring for a single headline proportion (0–100%), as on the
    dashboard's "Gross Margin" / "Project Progress" cards. One value
    against a neutral track — never a multi-slice donut. The figure in the
    middle uses text ink, not the series color. A null value renders an
    empty track with an em dash (no data), never a fake 0%.

    <x-ring :value="75.4" tone="text-[#C68F00] dark:text-[#BD8A00]" label="Gross margin" />
--}}
@props(['value' => null, 'tone' => 'text-accent-500', 'label' => ''])

@php
    $r = 54;
    $circumference = 2 * M_PI * $r;
    $pct = $value === null ? 0 : max(0, min(100, (float) $value));
    $offset = $circumference * (1 - $pct / 100);
@endphp

<div {{ $attributes->merge(['class' => 'relative mx-auto h-36 w-36']) }} title="{{ $label }}: {{ $value === null ? 'no data' : rtrim(rtrim(number_format($value, 1), '0'), '.').'%' }}">
    <svg viewBox="0 0 132 132" class="h-full w-full -rotate-90" role="img" aria-label="{{ $label }}: {{ $value === null ? 'no data' : number_format($value, 1).'%' }}">
        <circle cx="66" cy="66" r="{{ $r }}" fill="none" stroke-width="11" class="stroke-current text-ink-100" />
        @if ($value !== null && $pct > 0)
            <circle
                cx="66" cy="66" r="{{ $r }}" fill="none" stroke-width="11" stroke-linecap="round"
                stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $offset }}"
                class="stroke-current {{ $tone }} transition-[stroke-dashoffset] duration-700"
            />
        @endif
    </svg>
    <div class="absolute inset-0 flex items-center justify-center">
        <span class="text-3xl font-bold tracking-tight text-ink-900">
            {{ $value === null ? '—' : rtrim(rtrim(number_format($value, 1), '0'), '.').'%' }}
        </span>
    </div>
</div>
