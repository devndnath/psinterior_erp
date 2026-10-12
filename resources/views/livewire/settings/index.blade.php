<div x-data="{ tab: 'company' }">
    {{-- Phase 16: see reports/hr.blade.php — Settings had no heading of
         its own and relied on layouts.admin's old fallback <h1>. The tab
         strip and form fields below are still the pre-Phase-13 gray/
         brand styling (this page was never part of the Phase 15 sweep) —
         left as-is here; a full restyle of this page is its own task. --}}
    <x-page-header title="Settings" />

    <div class="border-b border-gray-200">
        <nav class="-mb-px flex gap-6">
            @foreach (['company' => 'Company', 'finance' => 'Finance & Tax', 'prefixes' => 'Numbering Prefixes', 'email' => 'Email'] as $key => $label)
                <button
                    type="button"
                    @click="tab = '{{ $key }}'"
                    :class="tab === '{{ $key }}' ? 'border-brand-600 text-brand-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap border-b-2 px-1 py-3 text-sm font-medium"
                >{{ $label }}</button>
            @endforeach
        </nav>
    </div>

    {{-- Company --}}
    <div x-show="tab === 'company'" x-cloak class="mt-6 max-w-xl">
        <form wire:submit="saveCompany" class="space-y-5">
            <div>
                <label class="form-label">Company name</label>
                <input wire:model="company_name" type="text" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('company_name') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Logo</label>
                <input wire:model="logo" type="file" accept="image/*" class="block w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-brand-700 hover:file:bg-brand-100">
                @error('logo') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Address</label>
                <textarea wire:model="company_address" rows="2" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Phone</label>
                    <input wire:model="company_phone" type="text" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <input wire:model="company_email" type="email" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                    @error('company_email') <p class="form-error">{{ $message }}</p> @enderror
                </div>
            </div>
            <div>
                <label class="form-label">Website</label>
                <input wire:model="company_website" type="text" placeholder="https://" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('company_website') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="btn-primary">Save company details</button>
        </form>
    </div>

    {{-- Finance / Tax --}}
    <div x-show="tab === 'finance'" x-cloak class="mt-6 max-w-xl">
        <form wire:submit="saveFinance" class="space-y-5">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Currency code</label>
                    <input wire:model="currency" type="text" maxlength="3" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm uppercase">
                    @error('currency') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Default tax / VAT rate (%)</label>
                    <input wire:model="tax_rate" type="number" step="0.01" min="0" max="100" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                    @error('tax_rate') <p class="form-error">{{ $message }}</p> @enderror
                </div>
            </div>
            <div>
                <label class="form-label">Date format</label>
                <select wire:model="date_format" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                    <option value="d M, Y">31 Dec, 2026</option>
                    <option value="d/m/Y">31/12/2026</option>
                    <option value="Y-m-d">2026-12-31</option>
                </select>
            </div>
            <p class="text-xs text-gray-500">Tax is applied everywhere from this one rate — no module hard-codes a percentage.</p>
            <button type="submit" class="btn-primary">Save finance settings</button>
        </form>
    </div>

    {{-- Numbering Prefixes --}}
    <div x-show="tab === 'prefixes'" x-cloak class="mt-6 max-w-xl">
        <form wire:submit="savePrefixes" class="space-y-5">
            <p class="text-sm text-gray-500">Controls the prefix each module's auto-generated number uses (e.g. <span class="font-mono">{{ $prefix_project }}-000001</span>). Already-issued numbers are never renumbered.</p>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Lead</label>
                    <input wire:model="prefix_lead" type="text" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm uppercase">
                </div>
                <div>
                    <label class="form-label">Client</label>
                    <input wire:model="prefix_client" type="text" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm uppercase">
                </div>
                <div>
                    <label class="form-label">Project</label>
                    <input wire:model="prefix_project" type="text" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm uppercase">
                </div>
                <div>
                    <label class="form-label">Quotation</label>
                    <input wire:model="prefix_quotation" type="text" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm uppercase">
                </div>
                <div>
                    <label class="form-label">Invoice</label>
                    <input wire:model="prefix_invoice" type="text" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm uppercase">
                </div>
                <div>
                    <label class="form-label">Purchase Order</label>
                    <input wire:model="prefix_purchase_order" type="text" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm uppercase">
                </div>
            </div>
            @error('prefix_lead') <p class="form-error">{{ $message }}</p> @enderror
            <button type="submit" class="btn-primary">Save prefixes</button>
        </form>
    </div>

    {{-- Email --}}
    <div x-show="tab === 'email'" x-cloak class="mt-6 max-w-xl">
        <form wire:submit="saveEmail" class="space-y-5">
            <div>
                <label class="form-label">From address</label>
                <input wire:model="mail_from_address" type="email" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('mail_from_address') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">From name</label>
                <input wire:model="mail_from_name" type="text" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('mail_from_name') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <p class="text-xs text-gray-500">SMTP transport credentials (host, port, username, password) are configured in the server's .env file, not here, since they're infrastructure secrets rather than business settings.</p>
            <button type="submit" class="btn-primary">Save email settings</button>
        </form>
    </div>
</div>
