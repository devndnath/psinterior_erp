<div>
    <x-page-header title="Warranty">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Warranties' => route('warranties.index'), 'Warranty' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @if ($warranty->isActive())
                <x-status-badge label="Active" color="green" />
            @else
                <x-status-badge label="Expired" color="gray" />
            @endif
            @can('update', $warranty)
                <x-button variant="secondary" href="{{ route('warranties.edit', $warranty) }}" wire:navigate>Edit</x-button>
            @endcan
            @can('delete', $warranty)
                <x-confirm-dialog
                    title="Delete this warranty?"
                    message="This will remove the warranty. This cannot be undone."
                    action="deleteWarranty"
                >
                    <x-slot:trigger>
                        <button type="button" class="btn-secondary text-danger-600">Delete</button>
                    </x-slot:trigger>
                </x-confirm-dialog>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <p class="-mt-4 mb-6 text-sm text-ink-500">
        @if ($warranty->project)
            <a href="{{ route('projects.show', $warranty->project) }}" wire:navigate class="text-accent-600 hover:underline">{{ $warranty->project->project_code }}</a>
            — {{ $warranty->project->name }}
        @endif
    </p>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Details</h2>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-ink-500">Start Date</dt><dd class="font-medium text-ink-900">{{ $warranty->start_date->format('d M, Y') }}</dd></div>
                    <div><dt class="text-ink-500">End Date</dt><dd class="font-medium text-ink-900">{{ $warranty->end_date->format('d M, Y') }}</dd></div>
                </dl>
                @if ($warranty->terms)
                    <p class="mt-4 whitespace-pre-line text-sm text-ink-700">{{ $warranty->terms }}</p>
                @endif
            </div>

            <div class="panel p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-ink-500">Service Requests</h2>
                    @can('create', \App\Models\ServiceRequest::class)
                        <a href="{{ route('service-requests.create', ['warranty' => $warranty->id]) }}" wire:navigate class="text-sm text-accent-600 hover:underline">+ New Service Request</a>
                    @endcan
                </div>
                @if ($warranty->serviceRequests->isEmpty())
                    <p class="text-sm text-ink-500">No service requests raised against this warranty yet.</p>
                @else
                    <ul class="divide-y divide-ink-100">
                        @foreach ($warranty->serviceRequests as $sr)
                            <li class="flex items-center justify-between py-2 text-sm">
                                <a href="{{ route('service-requests.show', $sr) }}" wire:navigate class="font-medium text-accent-600 hover:underline">{{ $sr->sr_no }}</a>
                                <x-status-badge :label="$sr->status->label()" :color="$sr->status->color()" />
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>
