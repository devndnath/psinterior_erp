<div>
    <x-breadcrumbs :items="['Subcontractors' => route('subcontractors.index'), $subcontractor->name => null]" class="mb-4" />

    <div class="mb-6 flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">{{ $subcontractor->name }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ $subcontractor->trade->label() }}{{ $subcontractor->rate_type ? ' — '.$subcontractor->rate_type : '' }}</p>
        </div>
        <div class="flex items-center gap-3">
            @can('create', \App\Models\SubcontractorAssignment::class)
                <a href="{{ route('subcontractor-assignments.create', ['subcontractor' => $subcontractor->id]) }}" wire:navigate class="btn-secondary">+ New Assignment</a>
            @endcan
            @can('update', $subcontractor)
                <a href="{{ route('subcontractors.edit', $subcontractor) }}" wire:navigate class="btn-secondary">Edit</a>
            @endcan
            @can('delete', $subcontractor)
                <x-confirm-dialog
                    title="Delete this subcontractor?"
                    :message='"This will remove {$subcontractor->name} and cannot be undone."'
                    action="deleteSubcontractor"
                >
                    <x-slot:trigger>
                        <button type="button" class="btn-secondary text-red-600">Delete</button>
                    </x-slot:trigger>
                </x-confirm-dialog>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Contact</h2>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-500">Phone</dt><dd class="font-medium text-gray-900">{{ $subcontractor->phone ?: '—' }}</dd></div>
                    <div><dt class="text-gray-500">Address</dt><dd class="font-medium text-gray-900">{{ $subcontractor->address ?: '—' }}</dd></div>
                </dl>
            </div>

            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Project Assignments</h2>
                @if ($assignments->isEmpty())
                    <p class="text-sm text-gray-500">No assignments yet.</p>
                @else
                    <div class="space-y-4">
                        @foreach ($assignments as $assignment)
                            <a href="{{ route('subcontractor-assignments.show', $assignment) }}" wire:navigate class="block rounded-lg border border-gray-100 p-4 hover:bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-gray-900">{{ $assignment->project?->project_code }} — {{ $assignment->project?->name }}</p>
                                    <p class="text-sm text-gray-500">{{ number_format($assignment->rate, 2) }}</p>
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
