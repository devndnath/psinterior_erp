<div>
    <x-page-header title="Record Payment">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Payments' => route('payments.index'), 'Record Payment' => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    <form wire:submit="save" class="max-w-2xl space-y-6">
        <div class="panel grid grid-cols-2 gap-4 p-6">
            <div class="col-span-2">
                <x-select label="Project *" name="project_id" wire:model.live="project_id">
                    <option value="">Select…</option>
                    @foreach ($projects as $proj)
                        <option value="{{ $proj->id }}">{{ $proj->project_code }} — {{ $proj->name }}</option>
                    @endforeach
                </x-select>
            </div>
            <div class="col-span-2">
                <x-select label="Invoice" name="invoice_id" wire:model="invoice_id">
                    <option value="">None — general payment</option>
                    @foreach ($invoices as $inv)
                        <option value="{{ $inv->id }}">{{ $inv->invoice_no }} — outstanding {{ number_format($inv->outstandingAmount(), 2) }}</option>
                    @endforeach
                </x-select>
            </div>
            <x-input label="Amount *" name="amount" type="number" step="0.01" wire:model="amount" />
            <x-input label="Date *" name="date" type="date" wire:model="date" />
            <x-select label="Method *" name="method" wire:model="method">
                @foreach ($methods as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-select>
            <x-input label="Reference" name="reference" type="text" placeholder="Cheque / transaction no." wire:model="reference" />
            <div class="col-span-2">
                <x-input label="Account" name="account" type="text" placeholder="Bank / cash account received into" wire:model="account" />
            </div>
            <div class="col-span-2">
                <x-textarea label="Notes" name="notes" rows="2" wire:model="notes" />
            </div>
        </div>

        <div class="flex items-center gap-3">
            <x-button type="submit">Record payment</x-button>
            <x-button variant="secondary" href="{{ route('payments.index') }}" wire:navigate>Cancel</x-button>
        </div>
    </form>
</div>
