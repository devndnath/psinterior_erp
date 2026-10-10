<div>
    <x-breadcrumbs :items="['Purchase Orders' => route('purchase-orders.index'), ($purchaseOrder ? 'Edit' : 'New') => null]" class="mb-4" />

    <h1 class="mb-6 text-2xl font-semibold text-gray-900">
        {{ $purchaseOrder ? "Edit Purchase Order {$purchaseOrder->po_no}" : 'New Purchase Order' }}
    </h1>

    @if ($vendorQuotation)
        <p class="mb-4 rounded-lg bg-blue-50 px-4 py-3 text-sm text-blue-700">
            Creating from vendor quotation on purchase request {{ $vendorQuotation->purchaseRequest->pr_no }}. Vendor and items are prefilled from that quotation — choose the project and receiving warehouse to continue.
        </p>
    @endif

    <form wire:submit="save" class="max-w-4xl space-y-6">
        <div class="card grid grid-cols-2 gap-4 p-6">
            <div>
                <label class="form-label">Vendor *</label>
                <select wire:model="vendor_id" @disabled($vendorQuotation) class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm disabled:bg-gray-50">
                    <option value="">Select…</option>
                    @foreach ($vendors as $v)
                        <option value="{{ $v->id }}">{{ $v->company_name }}</option>
                    @endforeach
                </select>
                @error('vendor_id') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Project *</label>
                <select wire:model="project_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                    <option value="">Select…</option>
                    @foreach ($projects as $proj)
                        <option value="{{ $proj->id }}">{{ $proj->project_code }} — {{ $proj->name }}</option>
                    @endforeach
                </select>
                @error('project_id') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Receiving Warehouse *</label>
                <select wire:model="warehouse_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                    <option value="">Select…</option>
                    @foreach ($warehouses as $w)
                        <option value="{{ $w->id }}">{{ $w->name }}</option>
                    @endforeach
                </select>
                @error('warehouse_id') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Date *</label>
                <input wire:model="date" type="date" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('date') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Expected Delivery</label>
                <input wire:model="expected_delivery" type="date" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('expected_delivery') <p class="form-error">{{ $message }}</p> @enderror
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
                <label class="form-label">Terms</label>
                <textarea wire:model="terms" rows="2" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"></textarea>
                @error('terms') <p class="form-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="card p-6">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Line items</h2>
                @unless ($vendorQuotation)
                    <button type="button" wire:click="addItem" class="text-sm text-brand-600 hover:underline">+ Add item</button>
                @endunless
            </div>

            <div class="space-y-3">
                @foreach ($items as $index => $item)
                    <div wire:key="po-item-{{ $index }}" class="grid grid-cols-12 gap-2 rounded-lg border border-gray-100 p-3">
                        <div class="col-span-6">
                            <select wire:model="items.{{ $index }}.product_id" @disabled($vendorQuotation) class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm disabled:bg-gray-50">
                                <option value="">Select a product…</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                                @endforeach
                            </select>
                            @error("items.{$index}.product_id") <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="col-span-2">
                            <input wire:model="items.{{ $index }}.qty" @disabled($vendorQuotation) type="number" step="0.01" placeholder="Qty" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm disabled:bg-gray-50">
                            @error("items.{$index}.qty") <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="col-span-3">
                            <input wire:model="items.{{ $index }}.rate" @disabled($vendorQuotation) type="number" step="0.01" placeholder="Rate" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm disabled:bg-gray-50">
                            @error("items.{$index}.rate") <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        @unless ($vendorQuotation)
                            <div class="col-span-1 flex items-center justify-end">
                                <button type="button" wire:click="removeItem({{ $index }})" class="text-sm text-red-500 hover:text-red-700">✕</button>
                            </div>
                        @endunless
                    </div>
                @endforeach
            </div>
            @if ($vendorQuotation)
                <p class="mt-3 text-xs text-gray-400">Items are copied from the vendor quotation and are not editable here.</p>
            @else
                <p class="mt-3 text-xs text-gray-400">Subtotal is calculated automatically as qty × rate, summed across items; total = subtotal − discount + tax.</p>
            @endif
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary">{{ $purchaseOrder ? 'Save changes' : 'Create purchase order' }}</button>
            <a href="{{ $purchaseOrder ? route('purchase-orders.show', $purchaseOrder) : route('purchase-orders.index') }}" wire:navigate class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
