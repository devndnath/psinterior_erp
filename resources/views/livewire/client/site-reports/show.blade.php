<div>
    <x-page-header :title="$siteReport->report_date?->format('d M, Y') ?? 'Site Report'">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Site Reports' => route('client.site-reports.index'), ($siteReport->report_date?->format('d M, Y') ?? 'Report') => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    <p class="-mt-4 mb-6 text-sm text-ink-500">{{ $siteReport->project?->name }}</p>

    <div class="space-y-6">
        <div class="panel p-6">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Site conditions</h2>
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div><dt class="text-ink-500">Weather</dt><dd class="font-medium text-ink-900">{{ $siteReport->weather ?: '—' }}</dd></div>
                <div><dt class="text-ink-500">Manpower on site</dt><dd class="font-medium text-ink-900">{{ $siteReport->manpower_count ?? '—' }}</dd></div>
            </dl>
        </div>

        <div class="panel p-6">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Work carried out</h2>
            <p class="whitespace-pre-line text-sm font-medium text-ink-900">{{ $siteReport->work_summary }}</p>
        </div>

        @if ($siteReport->materials_notes)
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Materials received / used</h2>
                <p class="whitespace-pre-line text-sm text-ink-900">{{ $siteReport->materials_notes }}</p>
            </div>
        @endif

        @if ($siteReport->issues)
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Issues / delays</h2>
                <p class="whitespace-pre-line text-sm text-ink-900">{{ $siteReport->issues }}</p>
            </div>
        @endif

        <div class="panel p-6">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Photos</h2>
            @if ($photos->isEmpty())
                <p class="text-sm text-ink-500">No photos uploaded.</p>
            @else
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                    @foreach ($photos as $photo)
                        <a href="{{ $photo->url() }}" target="_blank" class="block overflow-hidden rounded-lg border border-ink-100">
                            <img src="{{ $photo->url() }}" alt="{{ $photo->file_name }}" class="h-24 w-full object-cover">
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
