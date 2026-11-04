@props(['title' => 'Nothing here yet', 'description' => null, 'icon' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center rounded-3xl border-2 border-dashed border-ink-200 bg-surface/50 px-6 py-16 text-center']) }}>
    @if ($icon)
        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-accent-50 text-accent-600">
            {{ $icon }}
        </div>
    @endif

    <h3 class="text-sm font-semibold text-ink-900">{{ $title }}</h3>

    @if ($description)
        <p class="mt-1 max-w-sm text-sm text-ink-500">{{ $description }}</p>
    @endif

    @isset($action)
        <div class="mt-6">{{ $action }}</div>
    @endisset
</div>
