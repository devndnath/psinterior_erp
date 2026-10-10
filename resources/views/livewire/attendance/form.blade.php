<div>
    <x-breadcrumbs :items="['Attendance' => route('attendance.index'), ($attendance ? 'Edit' : 'New') => null]" class="mb-4" />

    <h1 class="mb-6 text-2xl font-semibold text-gray-900">
        {{ $attendance ? 'Edit Attendance' : 'Record Attendance' }}
    </h1>

    <form wire:submit="save" class="max-w-2xl space-y-6">
        <div class="card grid grid-cols-2 gap-4 p-6">
            <div class="col-span-2">
                <label class="form-label">Employee *</label>
                <select wire:model="employee_id" @disabled($attendance) class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm disabled:bg-gray-50">
                    <option value="">Select…</option>
                    @foreach ($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->employee_id }} — {{ $emp->name }}</option>
                    @endforeach
                </select>
                @error('employee_id') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Date *</label>
                <input wire:model="date" type="date" @disabled($attendance) class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm disabled:bg-gray-50">
                @error('date') <p class="form-error">{{ $message }}</p> @enderror
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
                <label class="form-label">Check In</label>
                <input wire:model="check_in" type="time" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('check_in') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Check Out</label>
                <input wire:model="check_out" type="time" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('check_out') <p class="form-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary">{{ $attendance ? 'Save changes' : 'Record attendance' }}</button>
            <a href="{{ route('attendance.index') }}" wire:navigate class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
