<div>
    <x-breadcrumbs :items="['Reports' => null]" class="mb-4" />

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($reports as $report)
            <a href="{{ route($report['route']) }}" wire:navigate class="card block p-6 transition hover:shadow-md">
                <h2 class="text-base font-semibold text-gray-900">{{ $report['label'] }}</h2>
                <p class="mt-2 text-sm text-gray-500">{{ $report['description'] }}</p>
            </a>
        @endforeach
    </div>
</div>
