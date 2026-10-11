<div>
    <x-page-header :title="$contract ? 'Edit Contract' : 'New Contract'" :description='$contract ? "Editing {$contract->contract_no}." : null'>
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Contracts' => route('contracts.index'), ($contract ? 'Edit' : 'New') => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    <form wire:submit="save" class="max-w-4xl space-y-6">
        <div class="panel grid grid-cols-3 gap-4 p-6">
            <x-select label="Project *" name="project_id" wire:model="project_id" :disabled="(bool) $contract">
                <option value="">Select…</option>
                @foreach ($projects as $proj)
                    <option value="{{ $proj->id }}">{{ $proj->project_code }} — {{ $proj->name }}</option>
                @endforeach
            </x-select>
            <x-input label="Amount *" name="amount" type="number" step="0.01" min="0" wire:model="amount" />
            <div></div>
            <x-input label="Start date" name="start_date" type="date" wire:model="start_date" />
            <x-input label="End date" name="end_date" type="date" wire:model="end_date" />
            <div></div>
            <div class="col-span-3">
                <x-textarea label="Payment terms" name="payment_terms" rows="2" wire:model="payment_terms" />
            </div>
            <div class="col-span-3">
                <x-textarea label="Warranty terms" name="warranty_terms" rows="2" wire:model="warranty_terms" />
            </div>
            <div class="col-span-3">
                <x-textarea label="Terms" name="terms" rows="2" wire:model="terms" />
            </div>
        </div>

        {{-- Repeating line-item rows (description/amount per row) — same
             "leave hand-rolled, reskin tokens" call as Quotations' and
             Site Visits' repeating fieldsets above. --}}
        <div class="panel p-6">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-ink-700">Line items</h2>
                <button type="button" wire:click="addItem" class="text-sm text-accent-600 hover:underline">+ Add item</button>
            </div>

            <div class="space-y-3">
                @foreach ($items as $index => $item)
                    <div wire:key="contract-item-{{ $index }}" class="grid grid-cols-12 gap-2 rounded-lg border border-ink-100 p-3">
                        <div class="col-span-9">
                            <input wire:model="items.{{ $index }}.description" type="text" placeholder="Description *" class="field-input">
                            @error("items.{$index}.description") <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="col-span-2">
                            <input wire:model="items.{{ $index }}.amount" type="number" step="0.01" min="0" placeholder="Amount" class="field-input">
                            @error("items.{$index}.amount") <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="col-span-1 flex items-center justify-end">
                            <button type="button" wire:click="removeItem({{ $index }})" class="text-sm text-danger-600 hover:text-danger-700">Remove</button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex items-center gap-3">
            <x-button type="submit">{{ $contract ? 'Save changes' : 'Create contract' }}</x-button>
            <x-button variant="secondary" href="{{ $contract ? route('contracts.show', $contract) : route('contracts.index') }}" wire:navigate>Cancel</x-button>
        </div>
    </form>
</div>
