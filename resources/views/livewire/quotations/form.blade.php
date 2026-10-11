<div>
    <x-page-header :title="$quotation ? 'Edit Quotation' : 'New Quotation'" :description='$quotation ? "Editing {$quotation->quotation_no}." : null'>
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Quotations' => route('quotations.index'), ($quotation ? 'Edit' : 'New') => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    @if ($quotation && ! $quotation->status->isEditableDirectly())
        <div class="mb-4 rounded-lg bg-warning-50 p-3 text-sm text-warning-700">
            This quotation has already been sent. Saving changes will snapshot the current version into its history, bump it to v{{ $quotation->version + 1 }}, and reset its status to Draft.
        </div>
    @endif

    <form wire:submit="save" class="max-w-5xl space-y-6">
        <div class="panel grid grid-cols-3 gap-4 p-6">
            <x-select label="Project *" name="project_id" wire:model="project_id" :disabled="(bool) $quotation">
                <option value="">Select…</option>
                @foreach ($projects as $proj)
                    <option value="{{ $proj->id }}">{{ $proj->project_code }} — {{ $proj->name }}</option>
                @endforeach
            </x-select>
            <x-input label="Valid until" name="valid_until" type="date" wire:model="valid_until" />
            <div></div>
            <x-input label="Discount" name="discount" type="number" step="0.01" min="0" wire:model="discount" />
            <x-input label="Tax" name="tax" type="number" step="0.01" min="0" wire:model="tax" />
            <div></div>
            <div class="col-span-3">
                <x-textarea label="Terms" name="terms" rows="2" wire:model="terms" />
            </div>
            <div class="col-span-3">
                <x-textarea label="Notes" name="notes" rows="2" wire:model="notes" />
            </div>
        </div>

        {{-- Repeating line-item rows (type/description/qty/unit/rate per
             row, an arbitrary number of rows) don't fit the single-field
             <x-input> label+error contract — same reasoning Phase 15a
             applied to Clients' contact list — so this stays hand-rolled,
             reskinned with the ink/accent/panel tokens. --}}
        <div class="panel p-6">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-ink-700">Line items</h2>
                <button type="button" wire:click="addItem" class="text-sm text-accent-600 hover:underline">+ Add item</button>
            </div>

            <div class="space-y-3">
                @foreach ($items as $index => $item)
                    <div wire:key="quotation-item-{{ $index }}" class="grid grid-cols-12 gap-2 rounded-lg border border-ink-100 p-3">
                        <div class="col-span-2">
                            <select wire:model="items.{{ $index }}.type" class="field-input">
                                @foreach ($itemTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-4">
                            <input wire:model="items.{{ $index }}.description" type="text" placeholder="Description *" class="field-input">
                            @error("items.{$index}.description") <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="col-span-2">
                            <input wire:model="items.{{ $index }}.qty" type="number" step="0.01" min="0" placeholder="Qty" class="field-input">
                            @error("items.{$index}.qty") <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="col-span-1">
                            <input wire:model="items.{{ $index }}.unit" type="text" placeholder="Unit" class="field-input">
                        </div>
                        <div class="col-span-2">
                            <input wire:model="items.{{ $index }}.rate" type="number" step="0.01" min="0" placeholder="Rate" class="field-input">
                            @error("items.{$index}.rate") <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="col-span-1 flex items-center justify-end">
                            <button type="button" wire:click="removeItem({{ $index }})" class="text-sm text-danger-600 hover:text-danger-700">Remove</button>
                        </div>
                    </div>
                @endforeach
            </div>
            <p class="mt-3 text-xs text-ink-400">Amount for each line is calculated automatically as qty × rate.</p>
        </div>

        <div class="flex items-center gap-3">
            <x-button type="submit">{{ $quotation ? 'Save changes' : 'Create quotation' }}</x-button>
            <x-button variant="secondary" href="{{ $quotation ? route('quotations.show', $quotation) : route('quotations.index') }}" wire:navigate>Cancel</x-button>
        </div>
    </form>
</div>
