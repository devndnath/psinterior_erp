{{--
    Mini daily bar sparkline (server-rendered SVG, no JS) for the
    dashboard KPI cards. $points is a list of ['label' => 'd M',
    'value' => float], oldest first. Bars sit on a shared baseline with a
    2px gap and rounded data-ends; a zero day draws a short neutral stub
    so the 14-day rhythm stays visible. Each bar carries a native <title>
    tooltip with its date and amount.

    <x-sparkbars :points="$income['spark']" tone="text-[#059669] dark:text-[#12A372]" currency="BDT" />
--}}
@props(['points' => [], 'tone' => 'text-accent-500', 'currency' => ''])

@php
    $barW = 7;
    $gap = 3;
    $h = 36;
    $count = max(count($points), 1);
    $w = $count * ($barW + $gap) - $gap;
    $max = max(array_merge([0], array_column($points, 'value')));
@endphp

<svg viewBox="0 0 {{ $w }} {{ $h }}" width="{{ $w }}" height="{{ $h }}" {{ $attributes->merge(['class' => 'block']) }} role="img" aria-label="Daily values, last {{ count($points) }} days">
    @foreach ($points as $i => $p)
        @php
            $barH = $max > 0 ? max(2, round($p['value'] / $max * $h, 1)) : 2;
            $x = $i * ($barW + $gap);
        @endphp
        <rect
            x="{{ $x }}" y="{{ $h - $barH }}" width="{{ $barW }}" height="{{ $barH }}" rx="2"
            class="fill-current {{ $p['value'] > 0 ? $tone : 'text-ink-200' }}"
        ><title>{{ $p['label'] }}: {{ trim($currency.' '.number_format($p['value'], 2)) }}</title></rect>
    @endforeach
</svg>
