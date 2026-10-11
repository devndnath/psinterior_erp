<div>
    <x-page-header title="Warranties" description="Track post-handover warranty coverage for projects.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Warranties' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @can('create', \App\Models\Warranty::class)
                <x-button href="{{ route('warranties.create') }}" wire:navigate>+ New Warranty</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="panel mb-4 flex flex-wrap items-end gap-4 p-4">
        <div>
            <x-select label="Project" name="project" wire:model.live="project">
                <option value="">Any project</option>
                @foreach ($projects as $proj)
                    <option value="{{ $proj->id }}">{{ $proj->project_code }} — {{ $proj->name }}</option>
                @endforeach
            </x-select>
        </div>
        <x-button variant="secondary" wire:click="resetFilters">Reset</x-button>
    </div>

    @if ($warranties->isEmpty())
        <div class="panel overflow-hidden">
            <x-empty-state
                title="No warranties found"
                description="Record a warranty to track post-handover coverage for a project."
            />
        </div>
    @else
        <x-table>
            <thead>
                <tr>
                    <th>Project</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($warranties as $warranty)
                    <tr wire:key="warranty-{{ $warranty->id }}">
                        <td>
                            <a href="{{ route('warranties.show', $warranty) }}" wire:navigate class="font-medium text-accent-600 hover:underline">
                                {{ $warranty->project?->project_code }}
                            </a>
                        </td>
                        <td>{{ $warranty->start_date->format('d M, Y') }}</td>
                        <td>{{ $warranty->end_date->format('d M, Y') }}</td>
                        <td>
                            @if ($warranty->isActive())
                                <x-status-badge label="Active" color="green" />
                            @else
                                <x-status-badge label="Expired" color="gray" />
                            @endif
                        </td>
                        <td class="text-right">
                            <a href="{{ route('warranties.show', $warranty) }}" wire:navigate class="text-ink-500 hover:text-ink-700">View</a>
                            @can('update', $warranty)
                                <a href="{{ route('warranties.edit', $warranty) }}" wire:navigate class="ml-3 text-ink-500 hover:text-ink-700">Edit</a>
                            @endcan
                            @can('delete', $warranty)
                                <span class="ml-3 inline-block">
                                    <x-confirm-dialog
                                        title="Delete this warranty?"
                                        message="This will remove the warranty. This cannot be undone."
                                        action="deleteWarranty"
                                        :params="[$warranty->id]"
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
            {{ $warranties->links() }}
        </div>
    @endif
</div>
