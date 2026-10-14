<div>
    <x-page-header title="Subcontractors" description="Manage subcontractors available to assign to projects.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Subcontractors' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @can('create', \App\Models\Subcontractor::class)
                <x-button href="{{ route('subcontractors.create') }}" wire:navigate>+ New Subcontractor</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="panel mb-4 flex flex-wrap items-end gap-4 p-4">
        <div class="min-w-[220px] flex-1">
            <x-input label="Search" name="search" wire:model.live.debounce.400ms="search" placeholder="Name…" />
        </div>
        <div>
            <x-select label="Trade" name="trade" wire:model.live="trade">
                <option value="">Any trade</option>
                @foreach ($trades as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-select>
        </div>
        <x-button variant="secondary" wire:click="resetFilters">Reset</x-button>
    </div>

    @if ($subcontractors->isEmpty())
        <div class="panel overflow-hidden">
            <x-empty-state
                title="No subcontractors found"
                description="Add a subcontractor to start assigning them to projects."
            />
        </div>
    @else
        <x-table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Trade</th>
                    <th>Phone</th>
                    <th>Rate Type</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($subcontractors as $subcontractor)
                    <tr wire:key="sub-{{ $subcontractor->id }}">
                        <td>
                            <a href="{{ route('subcontractors.show', $subcontractor) }}" wire:navigate class="font-medium text-accent-600 hover:underline">{{ $subcontractor->name }}</a>
                        </td>
                        <td>{{ $subcontractor->trade->label() }}</td>
                        <td>{{ $subcontractor->phone ?: '—' }}</td>
                        <td>{{ $subcontractor->rate_type ?: '—' }}</td>
                        <td class="text-right">
                            {{-- Phase 5 (premium-SaaS redesign): a single "..." action menu instead of several separate text links in every row. --}}
                            <x-dropdown>
                            <a href="{{ route('subcontractors.show', $subcontractor) }}" wire:navigate class="dropdown-item">View</a>
                            @can('update', $subcontractor)
                                <a href="{{ route('subcontractors.edit', $subcontractor) }}" wire:navigate class="dropdown-item">Edit</a>
                            @endcan
                            @can('delete', $subcontractor)
                                                                    <x-confirm-dialog
                                        title="Delete this subcontractor?"
                                        :message='"This will remove {$subcontractor->name} and cannot be undone."'
                                        action="deleteSubcontractor"
                                        :params="[$subcontractor->id]"
                                    >
                                        <x-slot:trigger>
                                            <button type="button" class="dropdown-item-danger">Delete</button>
                                        </x-slot:trigger>
                                    </x-confirm-dialog>
                            @endcan
                            </x-dropdown>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>
        <div class="mt-4">
            {{ $subcontractors->links() }}
        </div>
    @endif
</div>
