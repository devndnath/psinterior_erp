<div>
    <x-breadcrumbs :items="['Invoices' => route('vendor.invoices.index'), 'Submit' => null]" class="mb-4" />

    <h1 class="mb-6 text-2xl font-semibold text-ink-900">Submit Invoice</h1>

    <form wire:submit="save" class="max-w-2xl space-y-6">
        <div class="panel grid grid-cols-1 sm:grid-cols-2 gap-4 p-6">
            <div class="col-span-2">
                <x-select label="Purchase order *" name="purchase_order_id" wire:model="purchase_order_id">
                    <option value="">Select…</option>
                    @foreach ($purchaseOrders as $po)
                        <option value="{{ $po->id }}">{{ $po->po_no }} — {{ number_format($po->total, 2) }}</option>
                    @endforeach
                </x-select>
            </div>
            <x-input label="Your invoice no." name="vendor_invoice_no" wire:model="vendor_invoice_no" placeholder="The number on your own invoice" />
            <x-input label="Invoice date *" name="invoice_date" type="date" wire:model="invoice_date" />
            <x-input label="Amount *" name="amount" type="number" step="0.01" min="0" wire:model="amount" />
            <div class="col-span-2">
                <x-textarea label="Notes" name="notes" rows="2" wire:model="notes" />
            </div>
        </div>

        <div class="panel p-6">
            <h2 class="mb-4 text-sm font-semibold text-ink-700">Attach a copy (optional)</h2>
            <input wire:model="files" type="file" multiple class="block w-full text-sm text-ink-600 file:mr-4 file:rounded-lg file:border-0 file:bg-accent-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-accent-700 hover:file:bg-accent-100">
            @error('files.*') <p class="form-error">{{ $message }}</p> @enderror
            <div wire:loading wire:target="files" class="mt-2 text-xs text-ink-400">Uploading…</div>
        </div>

        <div class="flex items-center gap-3">
            <x-button type="submit">Submit invoice</x-button>
            <x-button variant="secondary" href="{{ route('vendor.invoices.index') }}" wire:navigate>Cancel</x-button>
        </div>
    </form>
</div>
