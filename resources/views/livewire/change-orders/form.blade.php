<div>
    <x-page-header :title="$changeOrder ? 'Edit Change Order' : 'New Change Order'" :description='$changeOrder ? "Editing {$changeOrder->co_no}." : null'>
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Change Orders' => route('change-orders.index'), ($changeOrder ? 'Edit' : 'New') => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    <form wire:submit="save" class="max-w-4xl space-y-6">
        <div class="panel grid grid-cols-2 gap-4 p-6">
            <div class="col-span-2">
                <x-select label="Project *" name="project_id" wire:model="project_id" :disabled="(bool) $changeOrder">
                    <option value="">Select…</option>
                    @foreach ($projects as $proj)
                        <option value="{{ $proj->id }}">{{ $proj->project_code }} — {{ $proj->name }}</option>
                    @endforeach
                </x-select>
            </div>
            <div class="col-span-2">
                <x-textarea label="Description" name="description" rows="2" wire:model="description" />
            </div>
        </div>

        {{-- Repeating line-item rows (description/qty/rate per row) — same
             "leave hand-rolled, reskin tokens" call as the other
             commercial modules' repeating fieldsets above. --}}
        <div class="panel p-6">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-ink-700">Line items</h2>
                <button type="button" wire:click="addItem" class="text-sm text-accent-600 hover:underline">+ Add item</button>
            </div>

            <div class="space-y-3">
                @foreach ($items as $index => $item)
                    <div wire:key="co-item-{{ $index }}" class="grid grid-cols-12 gap-2 rounded-lg border border-ink-100 p-3">
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
                            <button type="button" wire:click="removeItem({{ $index }})" class="text-sm text-danger-600 hover:text-danger-700">Remove</button>
                        </div>
                    </div>
                @endforeach
            </div>
            <p class="mt-3 text-xs text-ink-400">Rate can be negative for a scope reduction. Total cost is calculated automatically as qty × rate, summed across items.</p>
        </div>

        <div class="flex items-center gap-3">
            <x-button type="submit">{{ $changeOrder ? 'Save changes' : 'Create change order' }}</x-button>
            <x-button variant="secondary" href="{{ $changeOrder ? route('change-orders.show', $changeOrder) : route('change-orders.index') }}" wire:navigate>Cancel</x-button>
        </div>
    </form>
</div>
