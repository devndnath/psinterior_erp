<div>
    <x-page-header :title="$purchaseRequest->pr_no">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['RFQs' => route('vendor.rfqs.index'), $purchaseRequest->pr_no => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            <x-status-badge :label="$purchaseRequest->status->label()" :color="$purchaseRequest->status->color()" />
        </x-slot:actions>
    </x-page-header>

    <p class="-mt-4 mb-6 text-sm text-ink-500">{{ $purchaseRequest->project?->name }}</p>

    @if ($myQuotation)
        <div class="mb-6 panel p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-ink-500">Your quotation</h2>
                <x-status-badge :label="$myQuotation->status->label()" :color="$myQuotation->status->color()" />
            </div>
            @if ($myQuotation->status->value === 'rejected')
                <p class="mt-2 text-sm text-ink-500">This quotation was not selected for this request.</p>
            @elseif ($myQuotation->status->value === 'selected')
                <p class="mt-2 text-sm text-ink-500">Your quotation was selected and a purchase order will follow.</p>
            @endif
        </div>
    @endif

    <form wire:submit="submitQuote" class="panel p-6">
        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">
            {{ $myQuotation ? 'Your quoted items' : 'Quote these items' }}
        </h2>

        <div class="space-y-3">
            @foreach ($items as $index => $item)
                <div wire:key="rfq-item-{{ $index }}" class="grid grid-cols-12 gap-2 rounded-lg border border-ink-100 p-3">
                    <div class="col-span-5 flex items-center text-sm font-medium text-ink-900">{{ $item['product_name'] }}</div>
                    <div class="col-span-3">
                        <input wire:model="items.{{ $index }}.qty" type="number" step="0.01" min="0.01" placeholder="Qty" @disabled(! $canRespond) class="field-input">
                        @error("items.{$index}.qty") <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="col-span-4">
                        <input wire:model="items.{{ $index }}.rate" type="number" step="0.01" min="0" placeholder="Your rate" @disabled(! $canRespond) class="field-input">
                        @error("items.{$index}.rate") <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            @endforeach
        </div>

        @if ($canRespond)
            <div class="mt-4">
                <x-button type="submit">{{ $myQuotation ? 'Update quotation' : 'Submit quotation' }}</x-button>
            </div>
        @else
            <p class="mt-4 text-sm text-ink-400">This request is no longer open for quoting.</p>
        @endif
    </form>
</div>
