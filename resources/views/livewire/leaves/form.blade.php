<div>
    <x-breadcrumbs :items="['Leaves' => route('leaves.index'), 'New' => null]" class="mb-4" />

    <h1 class="mb-6 text-2xl font-semibold text-gray-900">New Leave Request</h1>

    <form wire:submit="save" class="max-w-2xl space-y-6">
        <div class="card grid grid-cols-2 gap-4 p-6">
            <div class="col-span-2">
                <label class="form-label">Employee *</label>
                <select wire:model="employee_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                    <option value="">Select…</option>
                    @foreach ($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->employee_id }} — {{ $emp->name }}</option>
                    @endforeach
                </select>
                @error('employee_id') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Type *</label>
                <select wire:model="type" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                    @foreach ($types as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('type') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div></div>
            <div>
                <label class="form-label">From *</label>
                <input wire:model="from_date" type="date" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('from_date') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">To *</label>
                <input wire:model="to_date" type="date" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('to_date') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div class="col-span-2">
                <label class="form-label">Reason</label>
                <textarea wire:model="reason" rows="3" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"></textarea>
                @error('reason') <p class="form-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary">Submit request</button>
            <a href="{{ route('leaves.index') }}" wire:navigate class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
