<div>
    <x-page-header :title="$vendor ? 'Edit Vendor' : 'New Vendor'" :description='$vendor ? "Editing {$vendor->vendor_code}." : null'>
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Vendors' => route('vendors.index'), ($vendor ? 'Edit' : 'New') => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    <form wire:submit="save" class="max-w-3xl space-y-6">
        <div class="panel grid grid-cols-2 gap-4 p-6">
            <div class="col-span-2">
                <x-input label="Company Name *" name="company_name" wire:model="company_name" />
            </div>
            <x-input label="Contact Person" name="contact_person" wire:model="contact_person" />
            <x-input label="Category" name="category" wire:model="category" placeholder="e.g. Hardware, Furniture" />
            <x-input label="Phone" name="phone" wire:model="phone" />
            <x-input :label="$create_account ? 'Email *' : 'Email'" name="email" type="email" wire:model="email" />
            <div class="col-span-2">
                <x-textarea label="Address" name="address" rows="2" wire:model="address" />
            </div>
            <x-input label="Tax Info" name="tax_info" wire:model="tax_info" />
            <div class="col-span-2">
                <x-textarea label="Notes" name="notes" rows="2" wire:model="notes" />
            </div>
        </div>

        @if (! ($vendor && $vendor->user_id))
            <div class="panel p-6">
                <label class="flex items-center gap-2">
                    <input wire:model.live="create_account" type="checkbox" class="rounded border-ink-300 text-accent-600 shadow-sm focus:ring-accent-500">
                    <span class="text-sm font-semibold text-ink-700">Give this vendor portal access</span>
                </label>
                @if ($create_account)
                    <p class="mt-2 text-xs text-ink-500">
                        Uses the email above. A temporary password is generated automatically and shown once, right here, after you save — this app doesn't send login emails, so copy it down and pass it to the vendor yourself.
                    </p>
                @endif
            </div>
        @endif

        <div class="flex items-center gap-3">
            <x-button type="submit">{{ $vendor ? 'Save changes' : 'Create vendor' }}</x-button>
            <x-button variant="secondary" href="{{ $vendor ? route('vendors.show', $vendor) : route('vendors.index') }}" wire:navigate>Cancel</x-button>
        </div>
    </form>
</div>
