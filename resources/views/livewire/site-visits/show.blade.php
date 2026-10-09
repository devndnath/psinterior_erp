<div>
    <x-breadcrumbs :items="['Site Visits' => route('site-visits.index'), $siteVisit->sv_no => null]" class="mb-4" />

    <div class="mb-6 flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">{{ $siteVisit->sv_no }}</h1>
            <p class="mt-1 text-sm text-gray-500">
                <a href="{{ route('clients.show', $siteVisit->client) }}" wire:navigate class="text-brand-600 hover:underline">{{ $siteVisit->client?->name }}</a>
                — {{ $siteVisit->visit_date?->format('d M, Y') }}
            </p>
        </div>
        @can('update', $siteVisit)
            <a href="{{ route('site-visits.edit', $siteVisit) }}" wire:navigate class="btn-secondary">Edit</a>
        @endcan
    </div>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Visit details</h2>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-500">Purpose</dt><dd class="font-medium text-gray-900">{{ $siteVisit->purpose ?: '—' }}</dd></div>
                    <div><dt class="text-gray-500">Assigned to</dt><dd class="font-medium text-gray-900">{{ $siteVisit->assignee?->name ?? '—' }}</dd></div>
                    <div class="col-span-2"><dt class="text-gray-500">Site address</dt><dd class="font-medium text-gray-900">{{ $siteVisit->site_address ?: '—' }}</dd></div>
                    <div class="col-span-2"><dt class="text-gray-500">Requirements</dt><dd class="whitespace-pre-line font-medium text-gray-900">{{ $siteVisit->requirements ?: '—' }}</dd></div>
                    <div class="col-span-2"><dt class="text-gray-500">Notes</dt><dd class="whitespace-pre-line font-medium text-gray-900">{{ $siteVisit->notes ?: '—' }}</dd></div>
                </dl>
            </div>

            <div class="card p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Rooms / Areas</h2>
                    <span class="text-sm text-gray-500">Total area: <span class="font-semibold text-gray-900">{{ number_format($siteVisit->totalArea(), 2) }}</span></span>
                </div>

                @if ($items->isEmpty())
                    <p class="text-sm text-gray-500">No rooms recorded.</p>
                @else
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Area</th>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Length</th>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Width</th>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Height</th>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Sq. area</th>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($items as $item)
                                <tr>
                                    <td class="px-3 py-2 text-sm font-medium text-gray-900">{{ $item->area_name }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-500">{{ $item->length ?? '—' }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-500">{{ $item->width ?? '—' }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-500">{{ $item->height ?? '—' }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-500">{{ $item->area ?? '—' }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-500">{{ $item->notes ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        <div>
            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Photos</h2>
                @if ($photos->isEmpty())
                    <p class="text-sm text-gray-500">No photos uploaded.</p>
                @else
                    <div class="grid grid-cols-2 gap-2">
                        @foreach ($photos as $photo)
                            <a href="{{ $photo->url() }}" target="_blank" class="block overflow-hidden rounded-lg border border-gray-100">
                                <img src="{{ $photo->url() }}" alt="{{ $photo->file_name }}" class="h-24 w-full object-cover">
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
