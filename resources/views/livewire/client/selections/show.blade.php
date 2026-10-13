<div>
    <x-page-header :title="$board->title">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Selections' => route('client.selections.index'), $board->title => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            <x-status-badge :label="$board->status->label()" :color="$board->status->color()" />
        </x-slot:actions>
    </x-page-header>

    <p class="-mt-4 mb-6 text-sm text-ink-500">
        {{ $board->project?->name }}
        @if ($board->area)
            — {{ $board->area }}
        @endif
    </p>

    @if ($board->description)
        <div class="mb-6 panel p-6">
            <p class="whitespace-pre-line text-sm text-ink-600">{{ $board->description }}</p>
        </div>
    @endif

    @if ($board->status->value === 'decided')
        <div class="panel mb-6 border-l-4 border-success-500 p-6">
            <h2 class="mb-1 text-sm font-semibold uppercase tracking-wide text-ink-500">Your Choice</h2>
            <p class="text-lg font-semibold text-ink-900">{{ $board->decidedOption?->label }}</p>
            <p class="mt-1 text-xs text-ink-500">Decided {{ $board->decided_at?->format('d M, Y H:i') }}</p>
            @if ($board->client_comment)
                <p class="mt-3 text-sm text-ink-600">{{ $board->client_comment }}</p>
            @endif
        </div>
    @else
        <div class="panel mb-6 p-6">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Please choose one option</h2>
            <p class="mb-4 text-sm text-ink-500">Review the options below and select the one you'd like to go with. This cannot be changed once submitted.</p>
        </div>
    @endif

    <div class="grid grid-cols-2 gap-4">
        @foreach ($board->options as $option)
            @php($isChosen = $board->decided_option_id === $option->id)
            <div wire:key="option-{{ $option->id }}" class="panel p-4 {{ $isChosen ? 'border-l-4 border-success-500' : '' }}">
                @if ($option->file)
                    <img src="{{ $option->file->url() }}" alt="{{ $option->label }}" class="mb-3 h-40 w-full rounded-lg object-cover">
                @else
                    <div class="mb-3 flex h-40 w-full items-center justify-center rounded-lg bg-ink-50 text-xs text-ink-400">No image</div>
                @endif

                <div class="flex items-start justify-between gap-2">
                    <p class="text-sm font-semibold text-ink-900">{{ $option->label }}</p>
                    @if ($isChosen)
                        <x-status-badge label="Your choice" color="green" />
                    @endif
                </div>

                @if ($option->price !== null)
                    <p class="mt-1 text-sm text-ink-700">{{ number_format((float) $option->price, 2) }}</p>
                @endif

                @if ($option->notes)
                    <p class="mt-2 text-xs text-ink-500">{{ $option->notes }}</p>
                @endif

                @if ($board->status->value === 'sent')
                    @if ($chosenOptionId === $option->id)
                        <form wire:submit="confirmChoice" class="mt-3 space-y-2 border-t border-ink-100 pt-3">
                            <x-textarea label="Comment (optional)" name="comment" rows="2" wire:model="comment" />
                            <div class="flex items-center gap-2">
                                <x-button type="submit">Confirm this choice</x-button>
                                <x-button variant="secondary" type="button" wire:click="cancelChoice">Cancel</x-button>
                            </div>
                        </form>
                    @else
                        <x-button variant="secondary" type="button" wire:click="chooseOption({{ $option->id }})" class="mt-3">
                            Choose this option
                        </x-button>
                    @endif
                @endif
            </div>
        @endforeach
    </div>
</div>
