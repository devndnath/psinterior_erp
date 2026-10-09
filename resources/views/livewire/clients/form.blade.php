<div>
    <x-breadcrumbs :items="['Clients' => route('clients.index'), ($client ? 'Edit' : 'New') => null]" class="mb-4" />

    <h1 class="mb-6 text-2xl font-semibold text-gray-900">
        {{ $client ? "Edit Client {$client->client_no}" : 'New Client' }}
    </h1>

    <form wire:submit="save" class="card max-w-3xl space-y-6 p-6">
        <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
                <label class="form-label">Name *</label>
                <input wire:model="name" type="text" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('name') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Type *</label>
                <select wire:model="type" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                    @foreach ($types as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Company name</label>
                <input wire:model="company_name" type="text" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
            </div>
            <div>
                <label class="form-label">Phone</label>
                <input wire:model="phone" type="text" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('phone') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Email</label>
                <input wire:model="email" type="email" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('email') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Tax ID</label>
                <input wire:model="tax_id" type="text" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
            </div>
            <div>
                <label class="form-label">Assigned to</label>
                <select wire:model="assigned_to" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                    <option value="">Unassigned</option>
                    @foreach ($assignees as $assignee)
                        <option value="{{ $assignee->id }}">{{ $assignee->name }}</option>
                    @endforeach
                </select>
                @error('assigned_to') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div class="col-span-2">
                <label class="form-label">Address</label>
                <textarea wire:model="address" rows="2" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"></textarea>
            </div>
            <div>
                <label class="form-label">Billing address</label>
                <textarea wire:model="billing_address" rows="2" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"></textarea>
            </div>
            <div>
                <label class="form-label">Site address</label>
                <textarea wire:model="site_address" rows="2" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"></textarea>
            </div>
            <div class="col-span-2">
                <label class="form-label">Notes</label>
                <textarea wire:model="notes" rows="3" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"></textarea>
                @error('notes') <p class="form-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex items-center gap-3 border-t border-gray-100 pt-5">
            <button type="submit" class="btn-primary">{{ $client ? 'Save changes' : 'Create client' }}</button>
            <a href="{{ $client ? route('clients.show', $client) : route('clients.index') }}" wire:navigate class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
