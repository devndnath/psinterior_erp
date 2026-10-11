<div>
    <x-page-header :title="$serviceRequest->sr_no">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Service Requests' => route('client.service-requests.index'), $serviceRequest->sr_no => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    <p class="-mt-4 mb-6 text-sm text-ink-500">{{ $serviceRequest->warranty?->project?->name }}</p>

    <div class="panel p-6">
        <dl class="space-y-3 text-sm">
            <div><dt class="text-ink-500">Status</dt><dd class="font-medium text-ink-900">{{ $serviceRequest->status->label() }}</dd></div>
            <div><dt class="text-ink-500">Complaint</dt><dd class="font-medium text-ink-900">{{ $serviceRequest->complaint }}</dd></div>
            <div><dt class="text-ink-500">Assigned Technician</dt><dd class="font-medium text-ink-900">{{ $serviceRequest->assignedTechnician?->name ?? '—' }}</dd></div>
        </dl>
    </div>
</div>
