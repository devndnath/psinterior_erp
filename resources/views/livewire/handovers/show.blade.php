<div>
    <x-breadcrumbs :items="[$handover->project->name => route('projects.show', $handover->project), 'Handover' => null]" class="mb-4" />

    <div class="mb-6 flex items-start justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold text-gray-900">Handover</h1>
                @if ($handover->isConfirmed())
                    <x-status-badge label="Client Confirmed" color="green" />
                @else
                    <x-status-badge label="Pending Confirmation" color="yellow" />
                @endif
            </div>
            <p class="mt-1 text-sm text-gray-500">
                <a href="{{ route('projects.show', $handover->project) }}" wire:navigate class="text-brand-600 hover:underline">{{ $handover->project->project_code }}</a>
                — {{ $handover->project->name }}
            </p>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Details</h2>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-500">Handover Date</dt><dd class="font-medium text-gray-900">{{ $handover->handover_date->format('d M, Y') }}</dd></div>
                    <div><dt class="text-gray-500">Client Confirmed At</dt><dd class="font-medium text-gray-900">{{ $handover->client_confirmed_at?->format('d M, Y H:i') ?? '—' }}</dd></div>
                </dl>
                @if ($handover->notes)
                    <p class="mt-4 whitespace-pre-line text-sm text-gray-700">{{ $handover->notes }}</p>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Client Confirmation</h2>
                <p class="mb-4 text-xs text-gray-400">No client portal yet — this records the client's confirmation on their behalf.</p>
                @can('confirmByClient', $handover)
                    <x-confirm-dialog
                        title="Confirm client handover?"
                        message="This records that the client has confirmed receipt of the handover."
                        action="confirmByClient"
                        :danger="false"
                    >
                        <x-slot:trigger>
                            <button type="button" class="btn-primary w-full">
                                {{ $handover->isConfirmed() ? 'Re-confirm Client Handover' : 'Confirm Client Handover' }}
                            </button>
                        </x-slot:trigger>
                    </x-confirm-dialog>
                @endcan
            </div>
        </div>
    </div>
</div>
