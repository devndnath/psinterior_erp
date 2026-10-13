<div>
    <x-page-header title="Reports" description="Pull up-to-date figures across the business, and export any report to CSV.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Reports' => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($reports as $report)
            <a href="{{ route($report['route']) }}" wire:navigate class="panel block p-6 transition hover:-translate-y-0.5 hover:ring-1 hover:ring-accent-200">
                <h2 class="text-base font-semibold text-ink-900">{{ $report['label'] }}</h2>
                <p class="mt-2 text-sm text-ink-500">{{ $report['description'] }}</p>
            </a>
        @endforeach
    </div>
</div>
