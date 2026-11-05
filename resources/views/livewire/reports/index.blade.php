<div>
    <x-page-header title="Reports" description="Pull up-to-date figures across the business, and export any report to CSV.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Reports' => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    {{-- Engine reports, grouped like the sidebar's Report Section. --}}
    @foreach (['accounts' => 'Accounts Report', 'crm' => 'CRM Report', 'all' => 'All Report'] as $key => $label)
        @if (($groups[$key] ?? collect())->isNotEmpty())
            <h2 class="mb-3 mt-2 px-1 text-xs font-bold uppercase tracking-[0.1em] text-ink-400">{{ $label }}</h2>
            <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($groups[$key] as $report)
                    <a href="{{ $report['url'] }}" wire:navigate class="panel group flex gap-4 p-5 transition hover:-translate-y-0.5">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-accent-500/15 text-accent-700 transition group-hover:bg-charcoal group-hover:text-accent-400 dark:text-accent-400">
                            <x-icon name="bar-chart" class="h-5 w-5" />
                        </span>
                        <span class="min-w-0">
                            <span class="block font-semibold text-ink-900">{{ $report['label'] }}</span>
                            <span class="mt-1 block text-sm text-ink-500">{{ $report['description'] }}</span>
                        </span>
                    </a>
                @endforeach
            </div>
        @endif
    @endforeach

    <h2 class="mb-3 mt-2 px-1 text-xs font-bold uppercase tracking-[0.1em] text-ink-400">Overview reports</h2>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($reports as $report)
            <a href="{{ route($report['route']) }}" wire:navigate class="panel group flex gap-4 p-5 transition hover:-translate-y-0.5">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-ink-100 text-ink-600 transition group-hover:bg-charcoal group-hover:text-accent-400">
                    <x-icon name="list" class="h-5 w-5" />
                </span>
                <span class="min-w-0">
                    <span class="block font-semibold text-ink-900">{{ $report['label'] }}</span>
                    <span class="mt-1 block text-sm text-ink-500">{{ $report['description'] }}</span>
                </span>
            </a>
        @endforeach
    </div>
</div>
