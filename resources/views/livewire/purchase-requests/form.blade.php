<div>
    <x-page-header :title="$purchaseRequest ? 'Edit Purchase Request' : 'New Purchase Request'" :description='$purchaseRequest ? "Editing {$purchaseRequest->pr_no}." : null'>
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Purchase Requests' => route('purchase-requests.index'), ($purchaseRequest ? 'Edit' : 'New') => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    <form wire:submit="save" class="max-w-4xl space-y-6">
        <div class="panel grid grid-cols-2 gap-4 p-6">
            <div class="col-span-2">
                <x-select label="Project *" name="project_id" wire:model="project_id" :disabled="(bool) $purchaseRequest">
                    <option value="">Select…</option>
                    @foreach ($projects as $proj)
                        <option value="{{ $proj->id }}">{{ $proj->project_code }} — {{ $proj->name }}</option>
                    @endforeach
                </x-select>
            </div>
        </div>

        {{-- Repeating item-row fieldset (purchase request items) stays
             hand-rolled/reskinned per the established precedent from
             BOQs/Invoices (Phase 15b/15d). --}}
        <div class="panel p-6">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-ink-700">Requested items</h2>
                <button type="button" wire:click="addItem" class="text-sm text-accent-600 hover:underline">+ Add item</button>
            </div>

            <div class="space-y-3">
                @foreach ($items as $index => $item)
                    <div wire:key="pr-item-{{ $index }}" class="grid grid-cols-12 gap-2 rounded-lg border border-ink-100 p-3">
                        <div class="col-span-8">
                            <select wire:model="items.{{ $index }}.product_id" class="field-input">
                                <option value="">Select a product…</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                                @endforeach
                            </select>
                            @error("items.{$index}.product_id") <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="col-span-3">
                            <input wire:model="items.{{ $index }}.quantity" type="number" step="0.01" placeholder="Quantity" class="field-input">
                            @error("items.{$index}.quantity") <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="col-span-1 flex items-center justify-end">
                            <button type="button" wire:click="removeItem({{ $index }})" class="text-sm text-danger-600 hover:text-danger-700">✕</button>
                        </div>
                    </div>
                @endforeach
            </div>
            <p class="mt-3 text-xs text-ink-400">No rate here — prices arrive with each vendor's quotation.</p>
        </div>

        <div class="flex items-center gap-3">
            <x-button type="submit">{{ $purchaseRequest ? 'Save changes' : 'Create purchase request' }}</x-button>
            <x-button variant="secondary" href="{{ $purchaseRequest ? route('purchase-requests.show', $purchaseRequest) : route('purchase-requests.index') }}" wire:navigate>Cancel</x-button>
        </div>
    </form>
</div>
