@props(['value' => 0, 'showLabel' => true])

@php($value = max(0, min(100, (int) $value)))

<div {{ $attributes->merge(['class' => 'w-full']) }}>
    <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100">
        <div
            class="h-full rounded-full transition-all duration-300 {{ $value >= 100 ? 'bg-green-500' : 'bg-brand-600' }}"
            style="width: {{ $value }}%"
        ></div>
    </div>
    @if ($showLabel)
        <span class="mt-1 block text-xs text-gray-500">{{ $value }}%</span>
    @endif
</div>
