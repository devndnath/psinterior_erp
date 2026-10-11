<div>
    <x-page-header title="Bills of Quantities" description="Estimate project costs line by line before quoting the client.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['BOQs' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @can('create', \App\Models\Boq::class)
                <x-button href="{{ route('boqs.create') }}" wire:navigate>+ New BOQ</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="panel mb-4 flex flex-wrap items-end gap-4 p-4">
        <div class="min-w-[220px] flex-1">
            <x-input label="Search" name="search" wire:model.live.debounce.400ms="search" placeholder="BOQ no…" />
        </div>
        <div>
            <x-select label="Project" name="project" wire:model.live="project">
                <option value="">Any project</option>
                @foreach ($projects as $proj)
                    <option value="{{ $proj->id }}">{{ $proj->project_code }} — {{ $proj->name }}</option>
                @endforeach
            </x-select>
        </div>
        <div>
            <x-select label="Status" name="status" wire:model.live="status">
                <option value="">Any status</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-select>
        </div>
        <x-button variant="secondary" wire:click="resetFilters">Reset</x-button>
    </div>

    @if ($boqs->isEmpty())
        <div class="panel overflow-hidden">
            <x-empty-state
                title="No BOQs found"
                description="Create a Bill of Quantities to start estimating a project's costs."
            />
        </div>
    @else
        <x-table>
            <thead>
                <tr>
                    <th>BOQ #</th>
                    <th>Project</th>
                    <th>Version</th>
                    <th>Grand Total</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($boqs as $boq)
                    <tr wire:key="boq-{{ $boq->id }}">
                        <td class="font-mono">
                            <a href="{{ route('boqs.show', $boq) }}" wire:navigate class="text-accent-600 hover:underline">{{ $boq->boq_no }}</a>
                        </td>
                        <td>
                            @if ($boq->project)
                                <a href="{{ route('projects.show', $boq->project) }}" wire:navigate class="hover:underline">{{ $boq->project->project_code }}</a>
                            @else
                                —
                            @endif
                        </td>
                        <td>v{{ $boq->version }}</td>
                        <td class="font-medium text-ink-900">{{ number_format($boq->grand_total, 2) }}</td>
                        <td>
                            <x-status-badge :label="$boq->status->label()" :color="$boq->status->color()" />
                        </td>
                        <td class="text-right">
                            <a href="{{ route('boqs.show', $boq) }}" wire:navigate class="text-ink-500 hover:text-ink-700">View</a>
                            @can('update', $boq)
                                <a href="{{ route('boqs.edit', $boq) }}" wire:navigate class="ml-3 text-ink-500 hover:text-ink-700">Edit</a>
                            @endcan
                            @can('delete', $boq)
                                <span class="ml-3 inline-block">
                                    <x-confirm-dialog
                                        title="Delete this BOQ?"
                                        :message='"This will remove {$boq->boq_no} and its items. This cannot be undone."'
                                        action="deleteBoq"
                                        :params="[$boq->id]"
                                    >
                                        <x-slot:trigger>
                                            <button type="button" class="text-danger-600 hover:text-danger-700">Delete</button>
                                        </x-slot:trigger>
                                    </x-confirm-dialog>
                                </span>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>
        <div class="mt-4">
            {{ $boqs->links() }}
        </div>
    @endif
</div>
