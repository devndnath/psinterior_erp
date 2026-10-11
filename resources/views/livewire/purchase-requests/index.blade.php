<div>
    <x-page-header title="Purchase Requests" description="Raise and track purchase requests before sourcing quotations from vendors.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Purchase Requests' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @can('create', \App\Models\PurchaseRequest::class)
                <x-button href="{{ route('purchase-requests.create') }}" wire:navigate>+ New Purchase Request</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="panel mb-4 flex flex-wrap items-end gap-4 p-4">
        <div class="min-w-[220px] flex-1">
            <x-input label="Search" name="search" wire:model.live.debounce.400ms="search" placeholder="PR no…" />
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

    @if ($purchaseRequests->isEmpty())
        <div class="panel overflow-hidden">
            <x-empty-state
                title="No purchase requests found"
                description="Raise a purchase request to start sourcing material from vendors."
            />
        </div>
    @else
        <x-table>
            <thead>
                <tr>
                    <th>PR #</th>
                    <th>Project</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($purchaseRequests as $pr)
                    <tr wire:key="pr-{{ $pr->id }}">
                        <td class="font-mono">
                            <a href="{{ route('purchase-requests.show', $pr) }}" wire:navigate class="text-accent-600 hover:underline">{{ $pr->pr_no }}</a>
                        </td>
                        <td>
                            @if ($pr->project)
                                <a href="{{ route('projects.show', $pr->project) }}" wire:navigate class="hover:underline">{{ $pr->project->project_code }}</a>
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            <x-status-badge :label="$pr->status->label()" :color="$pr->status->color()" />
                        </td>
                        <td class="text-right">
                            <a href="{{ route('purchase-requests.show', $pr) }}" wire:navigate class="text-ink-500 hover:text-ink-700">View</a>
                            @can('update', $pr)
                                <a href="{{ route('purchase-requests.edit', $pr) }}" wire:navigate class="ml-3 text-ink-500 hover:text-ink-700">Edit</a>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>
        <div class="mt-4">
            {{ $purchaseRequests->links() }}
        </div>
    @endif
</div>
