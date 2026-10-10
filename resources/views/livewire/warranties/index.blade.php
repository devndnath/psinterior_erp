<div>
    <x-breadcrumbs :items="['Warranties' => null]" class="mb-4" />

    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900">Warranties</h1>
        @can('create', \App\Models\Warranty::class)
            <a href="{{ route('warranties.create') }}" wire:navigate class="btn-primary">+ New Warranty</a>
        @endcan
    </div>

    <div class="card mb-4 flex flex-wrap items-end gap-4 p-4">
        <div>
            <label class="form-label">Project</label>
            <select wire:model.live="project" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                <option value="">Any project</option>
                @foreach ($projects as $proj)
                    <option value="{{ $proj->id }}">{{ $proj->project_code }} — {{ $proj->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="button" wire:click="resetFilters" class="btn-secondary">Reset</button>
    </div>

    <div class="card overflow-hidden">
        @if ($warranties->isEmpty())
            <x-empty-state
                title="No warranties found"
                description="Record a warranty to track post-handover coverage for a project."
            />
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Project</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Start</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">End</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach ($warranties as $warranty)
                        <tr wire:key="warranty-{{ $warranty->id }}" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-900">
                                <a href="{{ route('warranties.show', $warranty) }}" wire:navigate class="text-brand-600 hover:underline">
                                    {{ $warranty->project?->project_code }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $warranty->start_date->format('d M, Y') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $warranty->end_date->format('d M, Y') }}</td>
                            <td class="px-4 py-3 text-sm">
                                @if ($warranty->isActive())
                                    <x-status-badge label="Active" color="green" />
                                @else
                                    <x-status-badge label="Expired" color="gray" />
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right text-sm">
                                <a href="{{ route('warranties.show', $warranty) }}" wire:navigate class="text-gray-500 hover:text-gray-700">View</a>
                                @can('update', $warranty)
                                    <a href="{{ route('warranties.edit', $warranty) }}" wire:navigate class="ml-3 text-gray-500 hover:text-gray-700">Edit</a>
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
                {{ $warranties->links() }}
            </div>
        @endif
    </div>
</div>
