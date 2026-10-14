<div>
    <x-page-header title="New Leave Request">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Leaves' => route('leaves.index'), 'New' => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    <form wire:submit="save" class="max-w-2xl space-y-6">
        <div class="panel grid grid-cols-1 sm:grid-cols-2 gap-4 p-6">
            <div class="col-span-2">
                <x-select label="Employee *" name="employee_id" wire:model="employee_id">
                    <option value="">Select…</option>
                    @foreach ($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->employee_id }} — {{ $emp->name }}</option>
                    @endforeach
                </x-select>
            </div>
            <div>
                <x-select label="Type *" name="type" wire:model="type">
                    @foreach ($types as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-select>
            </div>
            <div></div>
            <div>
                <x-input label="From *" name="from_date" type="date" wire:model="from_date" />
            </div>
            <div>
                <x-input label="To *" name="to_date" type="date" wire:model="to_date" />
            </div>
            <div class="col-span-2">
                <x-textarea label="Reason" name="reason" rows="3" wire:model="reason" />
            </div>
        </div>

        <div class="flex items-center gap-3">
            <x-button type="submit">Submit request</x-button>
            <x-button variant="secondary" href="{{ route('leaves.index') }}" wire:navigate>Cancel</x-button>
        </div>
    </form>
</div>
