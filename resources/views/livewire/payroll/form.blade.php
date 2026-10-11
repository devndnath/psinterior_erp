<div>
    <x-page-header :title="$payroll ? 'Edit Payroll — '.$payroll->month : 'New Payroll'">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Payroll' => route('payroll.index'), ($payroll ? 'Edit' : 'New') => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    <form wire:submit="save" class="max-w-2xl space-y-6">
        <div class="panel grid grid-cols-2 gap-4 p-6">
            <div>
                <x-select label="Employee *" name="employee_id" wire:model="employee_id" :disabled="(bool) $payroll">
                    <option value="">Select…</option>
                    @foreach ($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->employee_id }} — {{ $emp->name }}</option>
                    @endforeach
                </x-select>
            </div>
            <div>
                <x-input label="Month *" name="month" type="month" wire:model="month" :disabled="(bool) $payroll" />
            </div>
            <div>
                <x-input label="Basic *" name="basic" type="number" step="0.01" wire:model.live="basic" />
            </div>
            <div>
                <x-input label="Allowance" name="allowance" type="number" step="0.01" wire:model.live="allowance" />
            </div>
            <div>
                <x-input label="Overtime" name="overtime" type="number" step="0.01" wire:model.live="overtime" />
            </div>
            <div>
                <x-input label="Bonus" name="bonus" type="number" step="0.01" wire:model.live="bonus" />
            </div>
            <div>
                <x-input label="Deduction" name="deduction" type="number" step="0.01" wire:model.live="deduction" />
            </div>
            <div>
                <x-input label="Advance" name="advance" type="number" step="0.01" wire:model.live="advance" />
            </div>
            <div class="col-span-2 rounded-lg bg-ink-50 p-4">
                <p class="text-sm text-ink-500">Net Salary (preview)</p>
                <p class="text-2xl font-semibold text-ink-900">{{ number_format($this->netSalary, 2) }}</p>
                <p class="mt-1 text-xs text-ink-400">basic + allowance + overtime + bonus − deduction − advance. Recalculated and stored on save.</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <x-button type="submit">{{ $payroll ? 'Save changes' : 'Generate payroll' }}</x-button>
            <x-button variant="secondary" href="{{ $payroll ? route('payroll.show', $payroll) : route('payroll.index') }}" wire:navigate>Cancel</x-button>
        </div>
    </form>
</div>
