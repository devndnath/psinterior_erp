<div>
    <x-page-header title="Handover">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="[$handover->project->name => route('projects.show', $handover->project), 'Handover' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @if ($handover->isConfirmed())
                <x-status-badge label="Client Confirmed" color="green" />
            @else
                <x-status-badge label="Pending Confirmation" color="yellow" />
            @endif
        </x-slot:actions>
    </x-page-header>

    <p class="-mt-4 mb-6 text-sm text-ink-500">
        <a href="{{ route('projects.show', $handover->project) }}" wire:navigate class="text-accent-600 hover:underline">{{ $handover->project->project_code }}</a>
        — {{ $handover->project->name }}
    </p>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Details</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-ink-500">Handover Date</dt><dd class="font-medium text-ink-900">{{ $handover->handover_date->format('d M, Y') }}</dd></div>
                    <div><dt class="text-ink-500">Client Confirmed At</dt><dd class="font-medium text-ink-900">{{ $handover->client_confirmed_at?->format('d M, Y H:i') ?? '—' }}</dd></div>
                </dl>
                @if ($handover->notes)
                    <p class="mt-4 whitespace-pre-line text-sm text-ink-700">{{ $handover->notes }}</p>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Client Confirmation</h2>
                <p class="mb-4 text-xs text-ink-400">No client portal yet — this records the client's confirmation on their behalf.</p>
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
