<div>
    <x-breadcrumbs :items="['Service Requests' => route('client.service-requests.index'), $serviceRequest->sr_no => null]" class="mb-4" />

    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">{{ $serviceRequest->sr_no }}</h1>
        <p class="mt-1 text-sm text-gray-500">{{ $serviceRequest->warranty?->project?->name }}</p>
    </div>

    <div class="card p-6">
        <dl class="space-y-3 text-sm">
            <div><dt class="text-gray-500">Status</dt><dd class="font-medium text-gray-900">{{ $serviceRequest->status->label() }}</dd></div>
            <div><dt class="text-gray-500">Complaint</dt><dd class="font-medium text-gray-900">{{ $serviceRequest->complaint }}</dd></div>
            <div><dt class="text-gray-500">Assigned Technician</dt><dd class="font-medium text-gray-900">{{ $serviceRequest->assignedTechnician?->name ?? '—' }}</dd></div>
        </dl>
    </div>
</div>
