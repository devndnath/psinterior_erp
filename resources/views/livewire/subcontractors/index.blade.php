<div>
    <x-breadcrumbs :items="['Subcontractors' => null]" class="mb-4" />

    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900">Subcontractors</h1>
        @can('create', \App\Models\Subcontractor::class)
            <a href="{{ route('subcontractors.create') }}" wire:navigate class="btn-primary">+ New Subcontractor</a>
        @endcan
    </div>

    <div class="card mb-4 flex flex-wrap items-end gap-4 p-4">
        <div class="min-w-[220px] flex-1">
            <label class="form-label">Search</label>
            <input wire:model.live.debounce.400ms="search" type="text" placeholder="Name…" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
        </div>
        <div>
            <label class="form-label">Trade</label>
            <select wire:model.live="trade" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                <option value="">Any trade</option>
                @foreach ($trades as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button type="button" wire:click="resetFilters" class="btn-secondary">Reset</button>
    </div>

    <div class="card overflow-hidden">
        @if ($subcontractors->isEmpty())
            <x-empty-state
                title="No subcontractors found"
                description="Add a subcontractor to start assigning them to projects."
            />
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Trade</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Phone</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Rate Type</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach ($subcontractors as $subcontractor)
                        <tr wire:key="sub-{{ $subcontractor->id }}" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-900">
                                <a href="{{ route('subcontractors.show', $subcontractor) }}" wire:navigate class="text-brand-600 hover:underline">{{ $subcontractor->name }}</a>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $subcontractor->trade->label() }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $subcontractor->phone ?: '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $subcontractor->rate_type ?: '—' }}</td>
                            <td class="px-4 py-3 text-right text-sm">
                                <a href="{{ route('subcontractors.show', $subcontractor) }}" wire:navigate class="text-gray-500 hover:text-gray-700">View</a>
                                @can('update', $subcontractor)
                                    <a href="{{ route('subcontractors.edit', $subcontractor) }}" wire:navigate class="ml-3 text-gray-500 hover:text-gray-700">Edit</a>
                                @endcan
                                @can('delete', $subcontractor)
                                    <span class="ml-3 inline-block">
                                        <x-confirm-dialog
                                            title="Delete this subcontractor?"
                                            :message='"This will remove {$subcontractor->name} and cannot be undone."'
                                            action="deleteSubcontractor"
                                            :params="[$subcontractor->id]"
                                        >
                                            <x-slot:trigger>
                                                <button type="button" class="text-red-500 hover:text-red-700">Delete</button>
                                            </x-slot:trigger>
                                        </x-confirm-dialog>
                                    </span>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="border-t border-gray-100 px-4 py-3">
                {{ $subcontractors->links() }}
            </div>
        @endif
    </div>
</div>
