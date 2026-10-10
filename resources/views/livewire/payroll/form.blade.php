<div>
    <x-breadcrumbs :items="['Payroll' => route('payroll.index'), ($payroll ? 'Edit' : 'New') => null]" class="mb-4" />

    <h1 class="mb-6 text-2xl font-semibold text-gray-900">
        {{ $payroll ? "Edit Payroll — {$payroll->month}" : 'New Payroll' }}
    </h1>

    <form wire:submit="save" class="max-w-2xl space-y-6">
        <div class="card grid grid-cols-2 gap-4 p-6">
            <div>
                <label class="form-label">Employee *</label>
                <select wire:model="employee_id" @disabled($payroll) class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm disabled:bg-gray-50">
                    <option value="">Select…</option>
                    @foreach ($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->employee_id }} — {{ $emp->name }}</option>
                    @endforeach
                </select>
                @error('employee_id') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Month *</label>
                <input wire:model="month" type="month" @disabled($payroll) class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm disabled:bg-gray-50">
                @error('month') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Basic *</label>
                <input wire:model.live="basic" type="number" step="0.01" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('basic') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Allowance</label>
                <input wire:model.live="allowance" type="number" step="0.01" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('allowance') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Overtime</label>
                <input wire:model.live="overtime" type="number" step="0.01" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('overtime') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Bonus</label>
                <input wire:model.live="bonus" type="number" step="0.01" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('bonus') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Deduction</label>
                <input wire:model.live="deduction" type="number" step="0.01" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('deduction') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Advance</label>
                <input wire:model.live="advance" type="number" step="0.01" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('advance') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div class="col-span-2 rounded-lg bg-gray-50 p-4">
                <p class="text-sm text-gray-500">Net Salary (preview)</p>
                <p class="text-2xl font-semibold text-gray-900">{{ number_format($this->netSalary, 2) }}</p>
                <p class="mt-1 text-xs text-gray-400">basic + allowance + overtime + bonus − deduction − advance. Recalculated and stored on save.</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary">{{ $payroll ? 'Save changes' : 'Generate payroll' }}</button>
            <a href="{{ $payroll ? route('payroll.show', $payroll) : route('payroll.index') }}" wire:navigate class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
