<div>
    <x-page-header :title="$client ? 'Edit Client' : 'New Client'" :description='$client ? "Editing {$client->client_no}." : null'>
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Clients' => route('clients.index'), ($client ? 'Edit' : 'New') => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    <form wire:submit="save" class="panel max-w-3xl space-y-6 p-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="col-span-2">
                <x-input label="Name *" name="name" wire:model="name" />
            </div>
            <x-select label="Type *" name="type" wire:model="type">
                @foreach ($types as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-select>
            <x-input label="Company name" name="company_name" wire:model="company_name" />
            <x-input label="Phone" name="phone" wire:model="phone" />
            <x-input :label="$create_account ? 'Email *' : 'Email'" name="email" type="email" wire:model="email" />
            <x-input label="Tax ID" name="tax_id" wire:model="tax_id" />
            <x-select label="Assigned to" name="assigned_to" wire:model="assigned_to">
                <option value="">Unassigned</option>
                @foreach ($assignees as $assignee)
                    <option value="{{ $assignee->id }}">{{ $assignee->name }}</option>
                @endforeach
            </x-select>
            <div class="col-span-2">
                <x-textarea label="Address" name="address" rows="2" wire:model="address" />
            </div>
            <x-textarea label="Billing address" name="billing_address" rows="2" wire:model="billing_address" />
            <x-textarea label="Site address" name="site_address" rows="2" wire:model="site_address" />
            <div class="col-span-2">
                <x-textarea label="Notes" name="notes" rows="3" wire:model="notes" />
            </div>
        </div>

        @if (! ($client && $client->user_id))
            <div class="border-t border-ink-100 pt-5">
                <label class="flex items-center gap-2">
                    <input wire:model.live="create_account" type="checkbox" class="rounded border-ink-300 text-accent-600 shadow-sm focus:ring-accent-500">
                    <span class="text-sm font-semibold text-ink-700">Give this client portal access</span>
                </label>
                @if ($create_account)
                    <p class="mt-2 text-xs text-ink-500">
                        Uses the email above. A temporary password is generated automatically and shown once, right here, after you save — this app doesn't send login emails, so copy it down and pass it to the client yourself.
                    </p>
                @endif
            </div>
        @endif

        <div class="flex items-center gap-3 border-t border-ink-100 pt-5">
            <x-button type="submit">{{ $client ? 'Save changes' : 'Create client' }}</x-button>
            <x-button variant="secondary" href="{{ $client ? route('clients.show', $client) : route('clients.index') }}" wire:navigate>Cancel</x-button>
        </div>
    </form>
</div>
