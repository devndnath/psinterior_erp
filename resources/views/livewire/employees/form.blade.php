<div>
    <x-breadcrumbs :items="['Employees' => route('employees.index'), ($employee ? 'Edit' : 'New') => null]" class="mb-4" />

    <h1 class="mb-6 text-2xl font-semibold text-gray-900">
        {{ $employee ? "Edit Employee {$employee->employee_id}" : 'New Employee' }}
    </h1>

    <form wire:submit="save" class="max-w-4xl space-y-6">
        <div class="card grid grid-cols-2 gap-4 p-6">
            <div class="col-span-2">
                <label class="form-label">Name *</label>
                <input wire:model="name" type="text" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('name') <p class="form-error">{{ $message }}</p> @enderror
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
            <div class="col-span-2">
                <label class="form-label">Address</label>
                <textarea wire:model="address" rows="2" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"></textarea>
                @error('address') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Department</label>
                <select wire:model.live="department_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                    <option value="">None</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
                @error('department_id') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Designation</label>
                <select wire:model="designation_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                    <option value="">None</option>
                    @foreach ($designations as $designation)
                        <option value="{{ $designation->id }}">{{ $designation->name }}</option>
                    @endforeach
                </select>
                @error('designation_id') <p class="form-error">{{ $message }}</p> @enderror
                <p class="mt-1 text-xs text-gray-400">Filtered to the selected department.</p>
            </div>
            <div>
                <label class="form-label">Joining Date *</label>
                <input wire:model="joining_date" type="date" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('joining_date') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Salary *</label>
                <input wire:model="salary" type="number" step="0.01" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('salary') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Employment Type *</label>
                <select wire:model="employment_type" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                    @foreach ($employmentTypes as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('employment_type') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Status *</label>
                <select wire:model="status" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('status') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Emergency Contact</label>
                <input wire:model="emergency_contact" type="text" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('emergency_contact') <p class="form-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary">{{ $employee ? 'Save changes' : 'Create employee' }}</button>
            <a href="{{ $employee ? route('employees.show', $employee) : route('employees.index') }}" wire:navigate class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
