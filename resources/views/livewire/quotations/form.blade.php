<div>
    <x-breadcrumbs :items="['Quotations' => route('quotations.index'), ($quotation ? 'Edit' : 'New') => null]" class="mb-4" />

    <h1 class="mb-6 text-2xl font-semibold text-gray-900">
        {{ $quotation ? "Edit Quotation {$quotation->quotation_no}" : 'New Quotation' }}
    </h1>

    @if ($quotation && ! $quotation->status->isEditableDirectly())
        <div class="mb-4 rounded-lg bg-yellow-50 p-3 text-sm text-yellow-700">
            This quotation has already been sent. Saving changes will snapshot the current version into its history, bump it to v{{ $quotation->version + 1 }}, and reset its status to Draft.
        </div>
    @endif

    <form wire:submit="save" class="max-w-5xl space-y-6">
        <div class="card grid grid-cols-3 gap-4 p-6">
            <div>
                <label class="form-label">Project *</label>
                <select wire:model="project_id" @disabled($quotation) class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm disabled:bg-gray-50">
                    <option value="">Select…</option>
                    @foreach ($projects as $proj)
                        <option value="{{ $proj->id }}">{{ $proj->project_code }} — {{ $proj->name }}</option>
                    @endforeach
                </select>
                @error('project_id') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Valid until</label>
                <input wire:model="valid_until" type="date" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('valid_until') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div></div>
            <div>
                <label class="form-label">Discount</label>
                <input wire:model="discount" type="number" step="0.01" min="0" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('discount') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Tax</label>
                <input wire:model="tax" type="number" step="0.01" min="0" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('tax') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div></div>
            <div class="col-span-3">
                <label class="form-label">Terms</label>
                <textarea wire:model="terms" rows="2" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"></textarea>
            </div>
            <div class="col-span-3">
                <label class="form-label">Notes</label>
                <textarea wire:model="notes" rows="2" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"></textarea>
            </div>
        </div>

        <div class="card p-6">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Line items</h2>
                <button type="button" wire:click="addItem" class="text-sm text-brand-600 hover:underline">+ Add item</button>
            </div>

            <div class="space-y-3">
                @foreach ($items as $index => $item)
                    <div wire:key="quotation-item-{{ $index }}" class="grid grid-cols-12 gap-2 rounded-lg border border-gray-100 p-3">
                        <div class="col-span-2">
                            <select wire:model="items.{{ $index }}.type" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                                @foreach ($itemTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-4">
                            <input wire:model="items.{{ $index }}.description" type="text" placeholder="Description *" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                            @error("items.{$index}.description") <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="col-span-2">
                            <input wire:model="items.{{ $index }}.qty" type="number" step="0.01" min="0" placeholder="Qty" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                            @error("items.{$index}.qty") <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="col-span-1">
                            <input wire:model="items.{{ $index }}.unit" type="text" placeholder="Unit" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                        </div>
                        <div class="col-span-2">
                            <input wire:model="items.{{ $index }}.rate" type="number" step="0.01" min="0" placeholder="Rate" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                            @error("items.{$index}.rate") <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="col-span-1 flex items-center justify-end">
                            <button type="button" wire:click="removeItem({{ $index }})" class="text-sm text-red-500 hover:text-red-700">✕</button>
                        </div>
                    </div>
                @endforeach
            </div>
            <p class="mt-3 text-xs text-gray-400">Amount for each line is calculated automatically as qty × rate.</p>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary">{{ $quotation ? 'Save changes' : 'Create quotation' }}</button>
            <a href="{{ $quotation ? route('quotations.show', $quotation) : route('quotations.index') }}" wire:navigate class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
