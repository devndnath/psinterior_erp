<div>
    <x-page-header title="Snags" description="Track a defect through to resolution.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Snags' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @can('create', \App\Models\Snag::class)
                <x-button href="{{ route('snags.create') }}" wire:navigate>+ New Snag</x-button>
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
        <div>
            <x-select label="Status" name="status" wire:model.live="status">
                <option value="">Any status</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-select>
        </div>
        <div>
            <x-select label="Assigned To" name="assigned_to" wire:model.live="assigned_to">
                <option value="">Anyone</option>
                @foreach ($users as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                @endforeach
            </x-select>
        </div>
        <x-button variant="secondary" wire:click="resetFilters">Reset</x-button>
    </div>

    @if ($snags->isEmpty())
        <div class="panel overflow-hidden">
            <x-empty-state
                title="No snags found"
                description="Raise a snag to track a defect through to resolution."
            />
        </div>
    @else
        <x-table>
            <thead>
                <tr>
                    <th>Snag #</th>
                    <th>Project</th>
                    <th>Issue</th>
                    <th>Assigned To</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($snags as $snag)
                    <tr wire:key="snag-{{ $snag->id }}">
                        <td class="font-mono">
                            <a href="{{ route('snags.show', $snag) }}" wire:navigate class="text-accent-600 hover:underline">{{ $snag->snag_no }}</a>
                        </td>
                        <td>
                            @if ($snag->project)
                                <a href="{{ route('projects.show', $snag->project) }}" wire:navigate class="hover:underline">{{ $snag->project->project_code }}</a>
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ \Illuminate\Support\Str::limit($snag->issue, 60) }}</td>
                        <td>{{ $snag->assignedTo?->name ?? '—' }}</td>
                        <td>
                            <x-status-badge :label="$snag->status->label()" :color="$snag->status->color()" />
                        </td>
                        <td class="text-right">
                            {{-- Phase 5 (premium-SaaS redesign): a single "..." action menu instead of several separate text links in every row. --}}
                            <x-dropdown>
                            <a href="{{ route('snags.show', $snag) }}" wire:navigate class="dropdown-item">View</a>
                            @can('delete', $snag)
                                                                    <x-confirm-dialog
                                        title="Delete this snag?"
                                        :message='"This will remove {$snag->snag_no}. This cannot be undone."'
                                        action="deleteSnag"
                                        :params="[$snag->id]"
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
            {{ $snags->links() }}
        </div>
    @endif
</div>
