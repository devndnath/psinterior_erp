{{--
    "Reference — who introduced them?" form section, shared by the Lead
    and Client forms (App\Livewire\Concerns\WithReferenceFields). Bound to
    the parent Livewire component's reference_* properties.

    <x-reference-fields :type="$reference_type" :clients="$referenceClients" :employees="$referenceEmployees" />
--}}
@props(['type' => '', 'clients' => [], 'employees' => []])

<div {{ $attributes->merge(['class' => 'col-span-2 rounded-3xl border border-ink-200/70 bg-ink-50/50 p-4 sm:p-5 dark:border-white/[0.06] dark:bg-white/[0.02]']) }}>
    <div class="mb-3 flex items-start gap-3">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-accent-500/15 text-accent-700 dark:text-accent-400">
            <x-icon name="users" class="h-4 w-4" />
        </span>
        <div>
            <p class="text-sm font-semibold text-ink-900">Reference</p>
            <p class="text-xs text-ink-500">Who introduced them to us? Shown on the References page.</p>
        </div>
    </div>

    <div class="mb-4 inline-flex flex-wrap gap-1 rounded-full border border-ink-200 bg-surface p-1">
        @foreach (['' => 'None', 'client' => 'Existing client', 'employee' => 'Staff member', 'other' => 'Someone else'] as $value => $label)
            <label @class([
                'cursor-pointer rounded-full px-3.5 py-1.5 text-sm font-medium transition',
                'bg-charcoal text-white dark:bg-accent-500 dark:text-charcoal' => $type === $value,
                'text-ink-600 hover:text-ink-900' => $type !== $value,
            ])>
                <input type="radio" class="sr-only" name="reference_type" value="{{ $value }}" wire:model.live="reference_type">
                {{ $label }}
            </label>
        @endforeach
    </div>

    @if ($type === 'client')
        <x-select label="Referred by client *" name="reference_client_id" wire:model="reference_client_id">
            <option value="">Select…</option>
            @foreach ($clients as $c)
                <option value="{{ $c->id }}">{{ $c->client_no }} — {{ $c->name }}{{ $c->phone ? ' · '.$c->phone : '' }}</option>
            @endforeach
        </x-select>
    @elseif ($type === 'employee')
        <x-select label="Referred by staff member *" name="reference_employee_id" wire:model="reference_employee_id">
            <option value="">Select…</option>
            @foreach ($employees as $e)
                <option value="{{ $e->id }}">{{ $e->name }}{{ $e->employee_id ? ' ('.$e->employee_id.')' : '' }}</option>
            @endforeach
        </x-select>
    @elseif ($type === 'other')
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <x-input label="Referrer name *" name="reference_name" wire:model="reference_name" placeholder="e.g. Architect friend, contractor…" />
            <x-input label="Referrer phone" name="reference_phone" wire:model="reference_phone" placeholder="+8801…" />
        </div>
    @endif
</div>
