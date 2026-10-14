<div>
    <x-page-header :title="$siteVisit->sv_no">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Site Visits' => route('site-visits.index'), $siteVisit->sv_no => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @can('update', $siteVisit)
                <x-button variant="secondary" href="{{ route('site-visits.edit', $siteVisit) }}" wire:navigate>Edit</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <p class="-mt-4 mb-6 text-sm text-ink-500">
        <a href="{{ route('clients.show', $siteVisit->client) }}" wire:navigate class="text-accent-600 hover:underline">{{ $siteVisit->client?->name }}</a>
        — {{ $siteVisit->visit_date?->format('d M, Y') }}
        @if ($siteVisit->project)
            — <a href="{{ route('projects.show', $siteVisit->project) }}" wire:navigate class="text-accent-600 hover:underline">{{ $siteVisit->project->project_code }}</a>
        @endif
    </p>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Visit details</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-ink-500">Purpose</dt><dd class="font-medium text-ink-900">{{ $siteVisit->purpose ?: '—' }}</dd></div>
                    <div><dt class="text-ink-500">Assigned to</dt><dd class="font-medium text-ink-900">{{ $siteVisit->assignee?->name ?? '—' }}</dd></div>
                    <div class="col-span-2"><dt class="text-ink-500">Site address</dt><dd class="font-medium text-ink-900">{{ $siteVisit->site_address ?: '—' }}</dd></div>
                    <div class="col-span-2"><dt class="text-ink-500">Requirements</dt><dd class="whitespace-pre-line font-medium text-ink-900">{{ $siteVisit->requirements ?: '—' }}</dd></div>
                    <div class="col-span-2"><dt class="text-ink-500">Notes</dt><dd class="whitespace-pre-line font-medium text-ink-900">{{ $siteVisit->notes ?: '—' }}</dd></div>
                </dl>
            </div>

            <div class="panel p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-ink-500">Rooms / Areas</h2>
                    <span class="text-sm text-ink-500">Total area: <span class="font-semibold text-ink-900">{{ number_format($siteVisit->totalArea(), 2) }}</span></span>
                </div>

                @if ($items->isEmpty())
                    <p class="text-sm text-ink-500">No rooms recorded.</p>
                @else
                    <div class="overflow-x-auto">
                    <table class="table-base">
                        <thead>
                            <tr>
                                <th>Area</th>
                                <th>Length</th>
                                <th>Width</th>
                                <th>Height</th>
                                <th>Sq. area</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $item)
                                <tr>
                                    <td class="font-medium text-ink-900">{{ $item->area_name }}</td>
                                    <td>{{ $item->length ?? '—' }}</td>
                                    <td>{{ $item->width ?? '—' }}</td>
                                    <td>{{ $item->height ?? '—' }}</td>
                                    <td>{{ $item->area ?? '—' }}</td>
                                    <td>{{ $item->notes ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                @endif
            </div>
        </div>

        <div>
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Photos</h2>
                @if ($photos->isEmpty())
                    <p class="text-sm text-ink-500">No photos uploaded.</p>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
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
</div>
