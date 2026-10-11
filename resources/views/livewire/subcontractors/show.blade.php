<div>
    <x-page-header :title="$subcontractor->name" :description="$subcontractor->trade->label().($subcontractor->rate_type ? ' — '.$subcontractor->rate_type : '')">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Subcontractors' => route('subcontractors.index'), $subcontractor->name => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @can('create', \App\Models\SubcontractorAssignment::class)
                <x-button variant="secondary" href="{{ route('subcontractor-assignments.create', ['subcontractor' => $subcontractor->id]) }}" wire:navigate>+ New Assignment</x-button>
            @endcan
            @can('update', $subcontractor)
                <x-button variant="secondary" href="{{ route('subcontractors.edit', $subcontractor) }}" wire:navigate>Edit</x-button>
            @endcan
            @can('delete', $subcontractor)
                <x-confirm-dialog
                    title="Delete this subcontractor?"
                    :message='"This will remove {$subcontractor->name} and cannot be undone."'
                    action="deleteSubcontractor"
                >
                    <x-slot:trigger>
                        <button type="button" class="btn-secondary text-danger-600">Delete</button>
                    </x-slot:trigger>
                </x-confirm-dialog>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Contact</h2>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-ink-500">Phone</dt><dd class="font-medium text-ink-900">{{ $subcontractor->phone ?: '—' }}</dd></div>
                    <div><dt class="text-ink-500">Address</dt><dd class="font-medium text-ink-900">{{ $subcontractor->address ?: '—' }}</dd></div>
                </dl>
            </div>

            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Project Assignments</h2>
                @if ($assignments->isEmpty())
                    <p class="text-sm text-ink-500">No assignments yet.</p>
                @else
                    <div class="space-y-4">
                        @foreach ($assignments as $assignment)
                            <a href="{{ route('subcontractor-assignments.show', $assignment) }}" wire:navigate class="block rounded-lg border border-ink-100 p-4 hover:bg-ink-50">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-ink-900">{{ $assignment->project?->project_code }} — {{ $assignment->project?->name }}</p>
                                    <p class="text-sm text-ink-500">{{ number_format($assignment->rate, 2) }}</p>
                                </div>
                                <div class="mt-2">
                                    <x-progress-bar :value="$assignment->progress" />
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
