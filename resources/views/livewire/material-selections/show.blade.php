<div>
    <x-page-header :title="$board->board_no">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Material Selections' => route('material-selections.index'), $board->board_no => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            <x-status-badge :label="$board->status->label()" :color="$board->status->color()" />
            @can('update', $board)
                <x-button variant="secondary" href="{{ route('material-selections.edit', $board) }}" wire:navigate>Edit</x-button>
            @endcan
            @can('manageOptions', $board)
                @if ($board->status->value === 'draft')
                    <x-confirm-dialog
                        title="Send this selection to the client?"
                        message="The client will be able to see all current options and pick one. Options can no longer be added or removed after this."
                        action="sendToClient"
                        confirmText="Send"
                        :danger="false"
                    >
                        <x-slot:trigger>
                            <button type="button" class="btn-primary">Send to Client</button>
                        </x-slot:trigger>
                    </x-confirm-dialog>
                @endif
                @if (in_array($board->status->value, ['draft', 'sent']))
                    <x-confirm-dialog
                        title="Cancel this selection?"
                        message="This withdraws the selection without recording a decision. This cannot be undone."
                        action="cancelBoard"
                    >
                        <x-slot:trigger>
                            <button type="button" class="btn-secondary text-danger-600">Cancel</button>
                        </x-slot:trigger>
                    </x-confirm-dialog>
                @endif
            @endcan
            @can('delete', $board)
                <x-confirm-dialog
                    title="Delete this selection?"
                    :message='"This will remove {$board->board_no} and all of its options. This cannot be undone."'
                    action="deleteBoard"
                >
                    <x-slot:trigger>
                        <button type="button" class="btn-secondary text-danger-600">Delete</button>
                    </x-slot:trigger>
                </x-confirm-dialog>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <p class="-mt-4 mb-6 text-sm text-ink-500">
        {{ $board->title }}
        @if ($board->area)
            — {{ $board->area }}
        @endif
        @if ($board->project)
            · <a href="{{ route('projects.show', $board->project) }}" wire:navigate class="text-accent-600 hover:underline">{{ $board->project->project_code }}</a>
        @endif
    </p>

    @if ($board->description)
        <div class="panel mb-6 p-6">
            <p class="whitespace-pre-line text-sm text-ink-700">{{ $board->description }}</p>
        </div>
    @endif

    @if ($board->status->value === 'decided' && $board->decidedOption)
        <div class="panel mb-6 border-l-4 border-success-500 p-6">
            <h2 class="mb-1 text-sm font-semibold uppercase tracking-wide text-ink-500">Client's Choice</h2>
            <p class="text-lg font-semibold text-ink-900">{{ $board->decidedOption->label }}</p>
            <p class="mt-1 text-xs text-ink-500">Decided {{ $board->decided_at?->format('d M, Y H:i') }}</p>
            @if ($board->client_comment)
                <p class="mt-3 rounded-lg bg-ink-50 p-3 text-sm text-ink-600">
                    <span class="font-medium text-ink-700">Client's comment:</span> {{ $board->client_comment }}
                </p>
            @endif
        </div>
    @endif

    <div class="panel p-6">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-ink-500">Options</h2>
            @can('manageOptions', $board)
                @if ($board->status->value === 'draft')
                    <button type="button" wire:click="toggleOptionForm" class="text-sm text-accent-600 hover:underline">
                        {{ $showOptionForm ? 'Cancel' : '+ Add option' }}
                    </button>
                @endif
            @endcan
        </div>

        @if ($showOptionForm)
            <form wire:submit="addOption" class="mb-6 space-y-3 rounded-lg border border-ink-100 p-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="col-span-2">
                        <x-input label="Label *" name="optionLabel" wire:model="optionLabel" placeholder="e.g. Option A — Carrara Marble Look" />
                    </div>
                    <x-select label="Catalog item (optional)" name="optionProductId" wire:model="optionProductId">
                        <option value="">None</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->sku }} — {{ $product->name }}</option>
                        @endforeach
                    </x-select>
                    <x-input label="Price" name="optionPrice" type="number" step="0.01" min="0" wire:model="optionPrice" />
                    <div class="col-span-2">
                        <x-input label="Reference Image" name="optionImage" type="file" accept="image/*" wire:model="optionImage" />
                        <div wire:loading wire:target="optionImage" class="mt-2 text-xs text-ink-400">Uploading…</div>
                    </div>
                    <div class="col-span-2">
                        <x-textarea label="Notes" name="optionNotes" rows="2" wire:model="optionNotes" />
                    </div>
                </div>
                <x-button type="submit">Add option</x-button>
            </form>
        @endif

        @if ($board->options->isEmpty())
            <p class="text-sm text-ink-500">No options added yet.</p>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($board->options as $option)
                    @php($isWinner = $board->decided_option_id === $option->id)
                    <div wire:key="option-{{ $option->id }}" class="rounded-lg border p-4 {{ $isWinner ? 'border-success-400 bg-success-50' : 'border-ink-100' }}">
                        @if ($option->file)
                            <img src="{{ $option->file->url() }}" alt="{{ $option->label }}" class="mb-3 h-32 w-full rounded-lg object-cover">
                        @else
                            <div class="mb-3 flex h-32 w-full items-center justify-center rounded-lg bg-ink-50 text-xs text-ink-400">No image</div>
                        @endif

                        <div class="flex items-start justify-between gap-2">
                            <p class="text-sm font-semibold text-ink-900">{{ $option->label }}</p>
                            @if ($isWinner)
                                <x-status-badge label="Chosen" color="green" />
                            @endif
                        </div>

                        @if ($option->product)
                            <p class="mt-1 text-xs text-ink-500">Catalog: {{ $option->product->sku }} — {{ $option->product->name }}</p>
                        @endif

                        @if ($option->price !== null)
                            <p class="mt-1 text-sm text-ink-700">{{ number_format((float) $option->price, 2) }}</p>
                        @endif

                        @if ($option->notes)
                            <p class="mt-2 text-xs text-ink-500">{{ $option->notes }}</p>
                        @endif

                        @can('manageOptions', $board)
                            @if ($board->status->value === 'draft')
                                <x-confirm-dialog
                                    title="Remove this option?"
                                    message="This will remove the option and its reference image."
                                    action="removeOption"
                                    :params="[$option->id]"
                                >
                                    <x-slot:trigger>
                                        <button type="button" class="mt-3 text-xs text-danger-600 hover:underline">Remove</button>
                                    </x-slot:trigger>
                                </x-confirm-dialog>
                            @endif
                        @endcan

                        @can('recordDecision', $board)
                            @if ($board->status->value === 'sent')
                                @if ($decidingOptionId === $option->id)
                                    <form wire:submit="recordDecision" class="mt-3 space-y-2 border-t border-ink-100 pt-3">
                                        <x-textarea label="Comment (optional)" name="decisionComment" rows="2" wire:model="decisionComment" />
                                        <div class="flex items-center gap-2">
                                            <x-button type="submit">Confirm</x-button>
                                            <x-button variant="secondary" type="button" wire:click="cancelDecisionForm">Cancel</x-button>
                                        </div>
                                    </form>
                                @else
                                    <button type="button" wire:click="openDecisionForm({{ $option->id }})" class="mt-3 text-xs text-accent-600 hover:underline">
                                        Record as client's choice
                                    </button>
                                @endif
                            @endif
                        @endcan
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
