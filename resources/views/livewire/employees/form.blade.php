<div>
    <x-page-header :title="$employee ? 'Edit Employee '.$employee->employee_id : 'New Employee'">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Employees' => route('employees.index'), ($employee ? 'Edit' : 'New') => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    <form wire:submit="save" class="max-w-4xl space-y-6">
        <div class="panel grid grid-cols-2 gap-4 p-6">
            <div class="col-span-2">
                <x-input label="Name *" name="name" wire:model="name" />
            </div>
            <div>
                <x-input label="Phone" name="phone" wire:model="phone" />
            </div>
            <div>
                <x-input label="Email" name="email" type="email" wire:model="email" />
            </div>
            <div class="col-span-2">
                <x-textarea label="Address" name="address" rows="2" wire:model="address" />
            </div>
            <div>
                <x-select label="Department" name="department_id" wire:model.live="department_id">
                    <option value="">None</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </x-select>
            </div>
            <div>
                <x-select label="Designation" name="designation_id" wire:model="designation_id">
                    <option value="">None</option>
                    @foreach ($designations as $designation)
                        <option value="{{ $designation->id }}">{{ $designation->name }}</option>
                    @endforeach
                </x-select>
                <p class="mt-1 text-xs text-ink-400">Filtered to the selected department.</p>
            </div>
            <div>
                <x-input label="Joining Date *" name="joining_date" type="date" wire:model="joining_date" />
            </div>
            <div>
                <x-input label="Salary *" name="salary" type="number" step="0.01" wire:model="salary" />
            </div>
            <div>
                <x-select label="Employment Type *" name="employment_type" wire:model="employment_type">
                    @foreach ($employmentTypes as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-select>
            </div>
            <div>
                <x-select label="Status *" name="status" wire:model="status">
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-select>
            </div>
            <div>
                <x-input label="Emergency Contact" name="emergency_contact" wire:model="emergency_contact" />
            </div>
        </div>

        <div class="flex items-center gap-3">
            <x-button type="submit">{{ $employee ? 'Save changes' : 'Create employee' }}</x-button>
            <x-button variant="secondary" href="{{ $employee ? route('employees.show', $employee) : route('employees.index') }}" wire:navigate>Cancel</x-button>
        </div>
    </form>
</div>
