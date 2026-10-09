<div>
    <x-breadcrumbs :items="['Contracts' => route('contracts.index'), ($contract ? 'Edit' : 'New') => null]" class="mb-4" />

    <h1 class="mb-6 text-2xl font-semibold text-gray-900">
        {{ $contract ? "Edit Contract {$contract->contract_no}" : 'New Contract' }}
    </h1>

    <form wire:submit="save" class="max-w-4xl space-y-6">
        <div class="card grid grid-cols-3 gap-4 p-6">
            <div>
                <label class="form-label">Project *</label>
                <select wire:model="project_id" @disabled($contract) class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm disabled:bg-gray-50">
                    <option value="">Select…</option>
                    @foreach ($projects as $proj)
                        <option value="{{ $proj->id }}">{{ $proj->project_code }} — {{ $proj->name }}</option>
                    @endforeach
                </select>
                @error('project_id') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Amount *</label>
                <input wire:model="amount" type="number" step="0.01" min="0" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('amount') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div></div>
            <div>
                <label class="form-label">Start date</label>
                <input wire:model="start_date" type="date" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
            </div>
            <div>
                <label class="form-label">End date</label>
                <input wire:model="end_date" type="date" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('end_date') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div></div>
            <div class="col-span-3">
                <label class="form-label">Payment terms</label>
                <textarea wire:model="payment_terms" rows="2" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"></textarea>
            </div>
            <div class="col-span-3">
                <label class="form-label">Warranty terms</label>
                <textarea wire:model="warranty_terms" rows="2" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"></textarea>
            </div>
            <div class="col-span-3">
                <label class="form-label">Terms</label>
                <textarea wire:model="terms" rows="2" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"></textarea>
            </div>
        </div>

        <div class="card p-6">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Line items</h2>
                <button type="button" wire:click="addItem" class="text-sm text-brand-600 hover:underline">+ Add item</button>
            </div>

            <div class="space-y-3">
                @foreach ($items as $index => $item)
                    <div wire:key="contract-item-{{ $index }}" class="grid grid-cols-12 gap-2 rounded-lg border border-gray-100 p-3">
                        <div class="col-span-9">
                            <input wire:model="items.{{ $index }}.description" type="text" placeholder="Description *" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                            @error("items.{$index}.description") <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="col-span-2">
                            <input wire:model="items.{{ $index }}.amount" type="number" step="0.01" min="0" placeholder="Amount" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                            @error("items.{$index}.amount") <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="col-span-1 flex items-center justify-end">
                            <button type="button" wire:click="removeItem({{ $index }})" class="text-sm text-red-500 hover:text-red-700">✕</button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary">{{ $contract ? 'Save changes' : 'Create contract' }}</button>
            <a href="{{ $contract ? route('contracts.show', $contract) : route('contracts.index') }}" wire:navigate class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
