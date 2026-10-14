<div>
    <x-breadcrumbs :items="['Expenses' => route('expenses.index'), ($expense ? 'Edit' : 'New') => null]" class="mb-4" />

    <h1 class="mb-6 text-2xl font-semibold text-gray-900">
        {{ $expense ? "Edit Expense {$expense->expense_no}" : 'New Expense' }}
    </h1>

    <form wire:submit="save" class="max-w-2xl space-y-6">
        <div class="card grid grid-cols-1 sm:grid-cols-2 gap-4 p-6">
            <div>
                <label class="form-label">Project *</label>
                <select wire:model="project_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                    <option value="">Select…</option>
                    @foreach ($projects as $proj)
                        <option value="{{ $proj->id }}">{{ $proj->project_code }} — {{ $proj->name }}</option>
                    @endforeach
                </select>
                @error('project_id') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Category *</label>
                <select wire:model="category_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                    <option value="">Select…</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
                @error('category_id') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Vendor (from directory)</label>
                <select wire:model="vendor_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                    <option value="">None</option>
                    @foreach ($vendors as $v)
                        <option value="{{ $v->id }}">{{ $v->company_name }}</option>
                    @endforeach
                </select>
                @error('vendor_id') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Vendor (free text, legacy)</label>
                <input wire:model="vendor_name" type="text" placeholder="Used only when no directory vendor is selected" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('vendor_name') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Amount *</label>
                <input wire:model="amount" type="number" step="0.01" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('amount') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Date *</label>
                <input wire:model="date" type="date" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('date') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Method *</label>
                <select wire:model="method" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                    @foreach ($methods as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('method') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div class="col-span-2">
                <label class="form-label">Description</label>
                <textarea wire:model="description" rows="2" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"></textarea>
                @error('description') <p class="form-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary">{{ $expense ? 'Save changes' : 'Create expense' }}</button>
            <a href="{{ $expense ? route('expenses.show', $expense) : route('expenses.index') }}" wire:navigate class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
