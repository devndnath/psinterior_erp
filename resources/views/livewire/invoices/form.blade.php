<div>
    <x-breadcrumbs :items="['Invoices' => route('invoices.index'), ($invoice ? 'Edit' : 'New') => null]" class="mb-4" />

    <h1 class="mb-6 text-2xl font-semibold text-gray-900">
        {{ $invoice ? "Edit Invoice {$invoice->invoice_no}" : 'New Invoice' }}
    </h1>

    <form wire:submit="save" class="max-w-4xl space-y-6">
        <div class="card grid grid-cols-2 gap-4 p-6">
            <div>
                <label class="form-label">Project *</label>
                <select wire:model.live="project_id" @disabled($invoice) class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm disabled:bg-gray-50">
                    <option value="">Select…</option>
                    @foreach ($projects as $proj)
                        <option value="{{ $proj->id }}">{{ $proj->project_code }} — {{ $proj->name }}</option>
                    @endforeach
                </select>
                @error('project_id') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Contract</label>
                <select wire:model="contract_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                    <option value="">None</option>
                    @foreach ($contracts as $contract)
                        <option value="{{ $contract->id }}">{{ $contract->contract_no }}</option>
                    @endforeach
                </select>
                @error('contract_id') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Type *</label>
                <select wire:model="type" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                    @foreach ($types as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('type') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Invoice date *</label>
                <input wire:model="invoice_date" type="date" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('invoice_date') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Due date</label>
                <input wire:model="due_date" type="date" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('due_date') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Discount</label>
                <input wire:model="discount" type="number" step="0.01" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('discount') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Tax</label>
                <input wire:model="tax" type="number" step="0.01" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('tax') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div class="col-span-2">
                <label class="form-label">Notes</label>
                <textarea wire:model="notes" rows="2" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"></textarea>
                @error('notes') <p class="form-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="card p-6">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Line items</h2>
                <button type="button" wire:click="addItem" class="text-sm text-brand-600 hover:underline">+ Add item</button>
            </div>

            <div class="space-y-3">
                @foreach ($items as $index => $item)
                    <div wire:key="invoice-item-{{ $index }}" class="grid grid-cols-12 gap-2 rounded-lg border border-gray-100 p-3">
                        <div class="col-span-7">
                            <input wire:model="items.{{ $index }}.description" type="text" placeholder="Description *" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                            @error("items.{$index}.description") <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="col-span-2">
                            <input wire:model="items.{{ $index }}.qty" type="number" step="0.01" placeholder="Qty" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                            @error("items.{$index}.qty") <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="col-span-2">
                            <input wire:model="items.{{ $index }}.rate" type="number" step="0.01" placeholder="Rate" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                            @error("items.{$index}.rate") <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="col-span-1 flex items-center justify-end">
                            <button type="button" wire:click="removeItem({{ $index }})" class="text-sm text-red-500 hover:text-red-700">✕</button>
                        </div>
                    </div>
                @endforeach
            </div>
            <p class="mt-3 text-xs text-gray-400">Subtotal, discount and tax are combined automatically into the grand total — qty × rate summed across items, minus discount, plus tax.</p>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary">{{ $invoice ? 'Save changes' : 'Create invoice' }}</button>
            <a href="{{ $invoice ? route('invoices.show', $invoice) : route('invoices.index') }}" wire:navigate class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
