<div>
    <x-page-header :title="$invoice ? 'Edit Invoice' : 'New Invoice'" :description='$invoice ? "Editing {$invoice->invoice_no}." : null'>
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Invoices' => route('invoices.index'), ($invoice ? 'Edit' : 'New') => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    <form wire:submit="save" class="max-w-4xl space-y-6">
        <div class="panel grid grid-cols-1 sm:grid-cols-2 gap-4 p-6">
            <x-select label="Project *" name="project_id" wire:model.live="project_id" :disabled="(bool) $invoice">
                <option value="">Select…</option>
                @foreach ($projects as $proj)
                    <option value="{{ $proj->id }}">{{ $proj->project_code }} — {{ $proj->name }}</option>
                @endforeach
            </x-select>
            <x-select label="Contract" name="contract_id" wire:model="contract_id">
                <option value="">None</option>
                @foreach ($contracts as $contract)
                    <option value="{{ $contract->id }}">{{ $contract->contract_no }}</option>
                @endforeach
            </x-select>
            <x-select label="Type *" name="type" wire:model="type">
                @foreach ($types as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-select>
            <x-input label="Invoice date *" name="invoice_date" type="date" wire:model="invoice_date" />
            <x-input label="Due date" name="due_date" type="date" wire:model="due_date" />
            <x-input label="Discount" name="discount" type="number" step="0.01" wire:model="discount" />
            <x-input label="Tax" name="tax" type="number" step="0.01" wire:model="tax" />
            <div class="col-span-2">
                <x-textarea label="Notes" name="notes" rows="2" wire:model="notes" />
            </div>
        </div>

        {{-- Repeating item-row fieldsets (invoice line items) stay
             hand-rolled/reskinned per the established precedent
             (Quotations/Contracts, Phase 15b). --}}
        <div class="panel p-6">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-ink-700">Line items</h2>
                <button type="button" wire:click="addItem" class="text-sm text-accent-600 hover:underline">+ Add item</button>
            </div>

            <div class="space-y-3">
                @foreach ($items as $index => $item)
                    <div wire:key="invoice-item-{{ $index }}" class="grid grid-cols-1 md:grid-cols-12 gap-2 rounded-lg border border-ink-100 p-3">
                        <div class="col-span-7">
                            <input wire:model="items.{{ $index }}.description" type="text" placeholder="Description *" class="field-input">
                            @error("items.{$index}.description") <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="col-span-2">
                            <input wire:model="items.{{ $index }}.qty" type="number" step="0.01" placeholder="Qty" class="field-input">
                            @error("items.{$index}.qty") <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="col-span-2">
                            <input wire:model="items.{{ $index }}.rate" type="number" step="0.01" placeholder="Rate" class="field-input">
                            @error("items.{$index}.rate") <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="col-span-1 flex items-center justify-end">
                            <button type="button" wire:click="removeItem({{ $index }})" class="text-sm text-danger-600 hover:text-danger-700">✕</button>
                        </div>
                    </div>
                @endforeach
            </div>
            <p class="mt-3 text-xs text-ink-400">Subtotal, discount and tax are combined automatically into the grand total — qty × rate summed across items, minus discount, plus tax.</p>
        </div>

        <div class="flex items-center gap-3">
            <x-button type="submit">{{ $invoice ? 'Save changes' : 'Create invoice' }}</x-button>
            <x-button variant="secondary" href="{{ $invoice ? route('invoices.show', $invoice) : route('invoices.index') }}" wire:navigate>Cancel</x-button>
        </div>
    </form>
</div>
