<div>
    <x-page-header :title="$attendance ? 'Edit Attendance' : 'Record Attendance'">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Attendance' => route('attendance.index'), ($attendance ? 'Edit' : 'New') => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    <form wire:submit="save" class="max-w-2xl space-y-6">
        <div class="panel grid grid-cols-1 sm:grid-cols-2 gap-4 p-6">
            <div class="col-span-2">
                <x-select label="Employee *" name="employee_id" wire:model="employee_id" :disabled="(bool) $attendance">
                    <option value="">Select…</option>
                    @foreach ($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->employee_id }} — {{ $emp->name }}</option>
                    @endforeach
                </x-select>
            </div>
            <div>
                <x-input label="Date *" name="date" type="date" wire:model="date" :disabled="(bool) $attendance" />
            </div>
            <div>
                <x-select label="Status *" name="status" wire:model="status">
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-select>
            </div>
            <div>
                <x-input label="Check In" name="check_in" type="time" wire:model="check_in" />
            </div>
            <div>
                <x-input label="Check Out" name="check_out" type="time" wire:model="check_out" />
            </div>
        </div>

        <div class="flex items-center gap-3">
            <x-button type="submit">{{ $attendance ? 'Save changes' : 'Record attendance' }}</x-button>
            <x-button variant="secondary" href="{{ route('attendance.index') }}" wire:navigate>Cancel</x-button>
        </div>
    </form>
</div>
