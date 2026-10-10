<div>
    <x-breadcrumbs :items="['Warranties' => route('warranties.index'), 'Warranty' => route('warranties.show', $serviceRequest->warranty), $serviceRequest->sr_no => null]" class="mb-4" />

    <div class="mb-6 flex items-start justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold text-gray-900">{{ $serviceRequest->sr_no }}</h1>
                <x-status-badge :label="$serviceRequest->status->label()" :color="$serviceRequest->status->color()" />
            </div>
            <p class="mt-1 text-sm text-gray-500">
                {{ $serviceRequest->client?->name }}
                —
                @if ($serviceRequest->warranty?->project)
                    <a href="{{ route('projects.show', $serviceRequest->warranty->project) }}" wire:navigate class="text-brand-600 hover:underline">{{ $serviceRequest->warranty->project->project_code }}</a>
                @endif
            </p>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Complaint</h2>
                <p class="whitespace-pre-line text-sm text-gray-900">{{ $serviceRequest->complaint }}</p>
            </div>

            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Details</h2>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-500">Assigned Technician</dt><dd class="font-medium text-gray-900">{{ $serviceRequest->assignedTechnician?->name ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Warranty</dt><dd class="font-medium text-gray-900">
                        <a href="{{ route('warranties.show', $serviceRequest->warranty) }}" wire:navigate class="text-brand-600 hover:underline">
                            {{ $serviceRequest->warranty->start_date->format('d M, Y') }} – {{ $serviceRequest->warranty->end_date->format('d M, Y') }}
                        </a>
                    </dd></div>
                </dl>
            </div>
        </div>

        <div class="space-y-6">
            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Workflow</h2>

                <div class="space-y-3">
                    @can('assignTechnician', $serviceRequest)
                        @if ($serviceRequest->status->value === 'open')
                            <div>
                                <label class="form-label">Technician *</label>
                                <select wire:model="assign_user_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                                    <option value="">Select…</option>
                                    @foreach ($users as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                                    @endforeach
                                </select>
                                @error('assign_user_id') <p class="form-error">{{ $message }}</p> @enderror
                            </div>
                            <x-confirm-dialog title="Assign a technician?" message="This moves the request to Technician Assigned." action="assignTechnician" :danger="false">
                                <x-slot:trigger><button type="button" class="btn-primary w-full">Assign Technician</button></x-slot:trigger>
                            </x-confirm-dialog>
                        @endif
                    @endcan

                    @can('scheduleSiteVisit', $serviceRequest)
                        @if ($serviceRequest->status->value === 'technician_assigned')
                            <x-confirm-dialog title="Schedule site visit?" message="This moves the request to Site Visit." action="scheduleSiteVisit" :danger="false">
                                <x-slot:trigger><button type="button" class="btn-primary w-full">Schedule Site Visit</button></x-slot:trigger>
                            </x-confirm-dialog>
                        @endif
                    @endcan

                    @can('markRepair', $serviceRequest)
                        @if ($serviceRequest->status->value === 'site_visit')
                            <x-confirm-dialog title="Mark under repair?" message="This moves the request to Repair." action="markRepair" :danger="false">
                                <x-slot:trigger><button type="button" class="btn-primary w-full">Mark Repair</button></x-slot:trigger>
                            </x-confirm-dialog>
                        @endif
                    @endcan

                    @can('markVerification', $serviceRequest)
                        @if ($serviceRequest->status->value === 'repair')
                            <x-confirm-dialog title="Move to verification?" message="This moves the request to Verification." action="markVerification" :danger="false">
                                <x-slot:trigger><button type="button" class="btn-primary w-full">Mark Verification</button></x-slot:trigger>
                            </x-confirm-dialog>
                        @endif
                    @endcan

                    @can('close', $serviceRequest)
                        @if ($serviceRequest->status->value === 'verification')
                            <x-confirm-dialog title="Close this request?" message="This moves the request to Closed." action="close">
                                <x-slot:trigger><button type="button" class="btn-primary w-full">Close</button></x-slot:trigger>
                            </x-confirm-dialog>
                        @endif
                    @endcan

                    @if ($serviceRequest->status->value === 'closed')
                        <p class="text-sm text-gray-500">This service request is closed.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
