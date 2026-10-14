<div>
    <x-page-header title="Material Selections" description="Let clients choose between tile, paint, hardware and finish options — tracked here instead of in ad-hoc notes or emails.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Material Selections' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @can('create', \App\Models\SelectionBoard::class)
                <x-button href="{{ route('material-selections.create') }}" wire:navigate>+ New Selection</x-button>
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
        <x-button variant="secondary" wire:click="resetFilters">Reset</x-button>
    </div>

    @if ($boards->isEmpty())
        <div class="panel overflow-hidden">
            <x-empty-state
                title="No selections found"
                description="Create a selection board to let a client choose between a set of material or finish options."
            />
        </div>
    @else
        <x-table>
            <thead>
                <tr>
                    <th>Board No.</th>
                    <th>Title</th>
                    <th>Area</th>
                    <th>Project</th>
                    <th>Options</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($boards as $board)
                    <tr wire:key="board-{{ $board->id }}">
                        <td>
                            <a href="{{ route('material-selections.show', $board) }}" wire:navigate class="font-medium text-accent-600 hover:underline">
                                {{ $board->board_no }}
                            </a>
                        </td>
                        <td>{{ $board->title }}</td>
                        <td>{{ $board->area ?: '—' }}</td>
                        <td>{{ $board->project?->project_code }}</td>
                        <td>{{ $board->options_count }}</td>
                        <td>
                            <x-status-badge :label="$board->status->label()" :color="$board->status->color()" />
                        </td>
                        <td class="text-right">
                            {{-- Phase 5 (premium-SaaS redesign): a single "..." action menu instead of several separate text links in every row. --}}
                            <x-dropdown>
                            <a href="{{ route('material-selections.show', $board) }}" wire:navigate class="dropdown-item">View</a>
                            </x-dropdown>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>
        <div class="mt-4">
            {{ $boards->links() }}
        </div>
    @endif
</div>
