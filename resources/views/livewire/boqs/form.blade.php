<div>
    <x-page-header :title="$boq ? 'Edit BOQ' : 'New BOQ'" :description='$boq ? "Editing {$boq->boq_no} (v{$boq->version})." : null'>
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['BOQs' => route('boqs.index'), ($boq ? 'Edit' : 'New') => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    <form wire:submit="save" class="max-w-5xl space-y-6">
        <div class="panel grid grid-cols-3 gap-4 p-6">
            <x-select label="Project *" name="project_id" wire:model="project_id" :disabled="(bool) $boq">
                <option value="">Select…</option>
                @foreach ($projects as $proj)
                    <option value="{{ $proj->id }}">{{ $proj->project_code }} — {{ $proj->name }}</option>
                @endforeach
            </x-select>
            <x-input label="Discount" name="discount" type="number" step="0.01" min="0" wire:model="discount" />
            <x-input label="Tax" name="tax" type="number" step="0.01" min="0" wire:model="tax" />
        </div>

        {{-- Repeating item-row fieldsets (BOQ items) stay hand-rolled/reskinned
             per the established precedent (Quotations/Contracts, Phase 15b). --}}
        <div class="panel p-6">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-ink-700">Line items</h2>
                <button type="button" wire:click="addItem" class="text-sm text-accent-600 hover:underline">+ Add item</button>
            </div>

            <div class="space-y-3">
                @foreach ($items as $index => $item)
                    <div wire:key="boq-item-{{ $index }}" class="grid grid-cols-12 gap-2 rounded-lg border border-ink-100 p-3">
                        <div class="col-span-3">
                            <input wire:model="items.{{ $index }}.item" type="text" placeholder="Item name *" class="field-input">
                            @error("items.{$index}.item") <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="col-span-2">
                            <input wire:model="items.{{ $index }}.category" type="text" placeholder="Category" class="field-input">
                        </div>
                        <div class="col-span-1">
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
                        <div class="col-span-2">
                            <input wire:model="items.{{ $index }}.remarks" type="text" placeholder="Remarks" class="field-input">
                        </div>
                        <div class="col-span-1 flex items-center justify-end">
                            <button type="button" wire:click="removeItem({{ $index }})" class="text-sm text-danger-600 hover:text-danger-700">✕</button>
                        </div>
                        <div class="col-span-6">
                            <input wire:model="items.{{ $index }}.description" type="text" placeholder="Description" class="field-input">
                        </div>
                        <div class="col-span-3">
                            <input wire:model="items.{{ $index }}.labour_cost" type="number" step="0.01" min="0" placeholder="Labour cost" class="field-input">
                        </div>
                        <div class="col-span-3">
                            <input wire:model="items.{{ $index }}.material_cost" type="number" step="0.01" min="0" placeholder="Material cost" class="field-input">
                        </div>
                    </div>
                @endforeach
            </div>
            <p class="mt-3 text-xs text-ink-400">Amount for each line is calculated automatically as qty × rate.</p>
        </div>

        <div class="flex items-center gap-3">
            <x-button type="submit">{{ $boq ? 'Save changes' : 'Create BOQ' }}</x-button>
            <x-button variant="secondary" href="{{ $boq ? route('boqs.show', $boq) : route('boqs.index') }}" wire:navigate>Cancel</x-button>
        </div>
    </form>
</div>
