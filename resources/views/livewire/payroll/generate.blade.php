<div>
    <x-page-header title="Generate Payroll" description="Compute a draft payroll from attendance, approved conveyance claims, bonus and overtime, then review before saving.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Payroll' => route('payroll.index'), 'Generate' => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <form wire:submit="save" class="col-span-2 space-y-6">
            <div class="panel grid grid-cols-1 sm:grid-cols-2 gap-4 p-6">
                <div>
                    <x-select label="Employee *" name="employee_id" wire:model.live="employee_id">
                        <option value="">Select…</option>
                        @foreach ($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->employee_id }} — {{ $emp->name }}</option>
                        @endforeach
                    </x-select>
                </div>
                <div>
                    <x-input label="Month *" name="month" type="month" wire:model.live="month" />
                </div>

                <div class="col-span-2 border-t border-ink-100 pt-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Computed from attendance &amp; claims — still editable</p>
                </div>

                <div>
                    <x-input label="Basic" name="basic" type="number" step="0.01" wire:model.live="basic" />
                </div>
                <div>
                    <x-input label="Allowance" name="allowance" type="number" step="0.01" wire:model.live="allowance" />
                </div>
                <div>
                    <x-input label="Conveyance" name="conveyance" type="number" step="0.01" wire:model.live="conveyance" />
                </div>
                <div>
                    <x-input label="Overtime" name="overtime" type="number" step="0.01" wire:model.live="overtime" />
                </div>
                <div>
                    <x-input label="Deduction" name="deduction" type="number" step="0.01" wire:model.live="deduction" />
                </div>
                <div></div>

                <div class="col-span-2 border-t border-ink-100 pt-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Manual</p>
                </div>

                <div>
                    <x-input label="Bonus" name="bonus" type="number" step="0.01" wire:model.live="bonus" />
                </div>
                <div>
                    <x-input label="Advance" name="advance" type="number" step="0.01" wire:model.live="advance" />
                </div>

                <div class="col-span-2 rounded-lg bg-ink-50 p-4">
                    <p class="text-sm text-ink-500">Net Salary (preview)</p>
                    <p class="text-2xl font-semibold text-ink-900">{{ number_format($this->netSalary, 2) }}</p>
                    <p class="mt-1 text-xs text-ink-400">basic + allowance + conveyance + overtime + bonus − deduction − advance.</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <x-button type="submit">Save as draft</x-button>
                <x-button variant="secondary" href="{{ route('payroll.index') }}" wire:navigate>Cancel</x-button>
            </div>
        </form>

        <div class="space-y-6">
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Attendance Summary</h2>
                @if ($summary)
                    <dl class="space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <dt class="text-ink-500">Days in month</dt>
                            <dd class="font-medium text-ink-900">{{ $summary['days_in_month'] }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-ink-500">Present</dt>
                            <dd class="font-medium text-ink-900">{{ $summary['present_days'] }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-ink-500">Absent (unpaid)</dt>
                            <dd class="font-medium text-danger-600">{{ $summary['absent_days'] }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-ink-500">On leave (paid)</dt>
                            <dd class="font-medium text-ink-900">{{ $summary['leave_days'] }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-ink-500">Overtime hours</dt>
                            <dd class="font-medium text-ink-900">{{ $summary['overtime_hours'] }}</dd>
                        </div>
                    </dl>
                @else
                    <p class="text-sm text-ink-500">Pick an employee and month to compute figures from their attendance record.</p>
                @endif
            </div>

            @if ($summary && $summary['claims']->isNotEmpty())
                <div class="panel p-6">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Conveyance Claims Included</h2>
                    <ul class="space-y-2 text-sm">
                        @foreach ($summary['claims'] as $claim)
                            <li class="flex items-center justify-between">
                                <span class="text-ink-500">{{ $claim->date->format('d M') }} · {{ $claim->type->label() }}</span>
                                <span class="font-medium text-ink-900">{{ number_format($claim->amount, 2) }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <p class="mt-3 text-xs text-ink-400">These Approved claims are marked as included once this payroll is saved, so they won't be pulled into a future run.</p>
                </div>
            @endif
        </div>
    </div>
</div>
