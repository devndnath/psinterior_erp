<div>
    <x-page-header :title="$claim ? 'Edit Expense Claim' : 'New Expense Claim'">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Expense Claims' => route('expense-claims.index'), ($claim ? 'Edit' : 'New') => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    <form wire:submit="save" class="max-w-2xl space-y-6">
        <div class="panel grid grid-cols-1 sm:grid-cols-2 gap-4 p-6">
            <div class="col-span-2">
                <x-select label="Employee *" name="employee_id" wire:model="employee_id" :disabled="(bool) $claim">
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
            <div>
                <x-input label="Date *" name="date" type="date" wire:model="date" />
            </div>
            <div>
                <x-select label="Project" name="project_id" wire:model="project_id">
                    <option value="">None</option>
                    @foreach ($projects as $proj)
                        <option value="{{ $proj->id }}">{{ $proj->project_code }} — {{ $proj->name }}</option>
                    @endforeach
                </x-select>
            </div>
            <div>
                <x-select label="Site Visit" name="site_visit_id" wire:model="site_visit_id">
                    <option value="">None</option>
                    @foreach ($siteVisits as $visit)
                        <option value="{{ $visit->id }}">{{ $visit->sv_no }} — {{ $visit->visit_date->format('d M, Y') }}</option>
                    @endforeach
                </x-select>
            </div>
            <div>
                <x-input label="Amount *" name="amount" type="number" step="0.01" wire:model="amount" />
            </div>
            <div></div>
            <div class="col-span-2">
                <x-textarea label="Description" name="description" rows="3" wire:model="description" />
            </div>
        </div>

        <div class="flex items-center gap-3">
            <x-button type="submit">{{ $claim ? 'Save changes' : 'Submit claim' }}</x-button>
            <x-button variant="secondary" href="{{ $claim ? route('expense-claims.show', $claim) : route('expense-claims.index') }}" wire:navigate>Cancel</x-button>
        </div>
    </form>
</div>
