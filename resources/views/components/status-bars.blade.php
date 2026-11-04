{{--
    Horizontal status bars, as in the reference's "Order Status" card: one
    row per status with its label and share on top, then a rounded bar
    whose filled part carries the count in a small white chip. Single
    series, so one color (brand gold) and no legend — the label names each
    row. The remainder is a plain neutral track (texture is reserved for
    accessibility/print, never decoration). Statuses with no records are
    left out; if every count is zero the empty message shows instead.

    <x-status-bars :rows="[['label' => 'Paid', 'count' => 12], ...]" unit="invoices" empty="No invoices yet." />

    `compact` draws one tight row per status (label, thin bar, count) for
    long lists such as the CRM overview's pipeline and project stages.
--}}
@props(['rows' => [], 'unit' => 'records', 'empty' => 'Nothing recorded yet.', 'href' => null, 'compact' => false])

@php
    $rows = collect($rows)->filter(fn ($r) => ($r['count'] ?? 0) > 0)->values();
    $total = $rows->sum('count');
@endphp

<div {{ $attributes->merge(['class' => $compact ? 'space-y-2.5' : 'space-y-4']) }}>
    @forelse ($rows as $row)
        @php $pct = $total > 0 ? $row['count'] / $total * 100 : 0; @endphp
        @if ($compact)
            <div class="grid grid-cols-[minmax(0,7.5rem)_1fr_auto] items-center gap-3 text-sm" title="{{ $row['label'] }}: {{ number_format($row['count']) }} {{ $unit }} ({{ round($pct) }}%)">
                <span class="truncate font-medium text-ink-700">{{ $row['label'] }}</span>
                <span class="h-2 overflow-hidden rounded-full bg-ink-100">
                    <span class="block h-full rounded-full bg-accent-500" style="width: {{ max(2, min(100, $pct)) }}%"></span>
                </span>
                <span class="w-16 text-right text-xs tabular-nums text-ink-500"><span class="font-semibold text-ink-900">{{ number_format($row['count']) }}</span> · {{ round($pct) }}%</span>
            </div>
            @continue
        @endif
        <div title="{{ $row['label'] }}: {{ number_format($row['count']) }} {{ $unit }} ({{ round($pct) }}%)">
            <div class="mb-1.5 flex items-baseline justify-between gap-3 text-sm">
                <span class="truncate font-medium text-ink-700">{{ $row['label'] }}</span>
                <span class="shrink-0 text-xs font-semibold tabular-nums text-ink-500">{{ round($pct) }}%</span>
            </div>
            <div class="h-8 overflow-hidden rounded-full bg-ink-100/80 dark:bg-ink-100">
                <div class="flex h-full min-w-[3.25rem] items-center justify-end rounded-full bg-accent-500 pr-1 transition-[width] duration-700" style="width: {{ max(0, min(100, $pct)) }}%">
                    <span class="rounded-full bg-surface px-2 py-0.5 text-xs font-semibold tabular-nums text-ink-900">{{ number_format($row['count']) }}</span>
                </div>
            </div>
        </div>
    @empty
        <p class="py-8 text-center text-sm text-ink-500">{{ $empty }}</p>
    @endforelse
</div>
