@props(['title' => 'Nothing here yet', 'description' => null, 'icon' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-200 px-6 py-16 text-center']) }}>
    @if ($icon)
        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400">
            {{ $icon }}
        </div>
    @endif

    <h3 class="text-sm font-semibold text-gray-900">{{ $title }}</h3>

    @if ($description)
        <p class="mt-1 max-w-sm text-sm text-gray-500">{{ $description }}</p>
    @endif

    @isset($action)
        <div class="mt-6">{{ $action }}</div>
    @endisset
</div>
