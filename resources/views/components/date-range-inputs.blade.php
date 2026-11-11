{{--
    From / To date inputs for a list's filter panel — pairs with the
    App\Livewire\Concerns\WithDateRange trait (?from=…&to=…).

    <x-date-range-inputs label="Created" :active="$from || $to" />
--}}
@props(['label' => 'Date', 'active' => false])

<div class="w-[9.5rem]">
    <x-input :label="$label.' from'" name="from" type="date" wire:model.live="from" />
</div>
<div class="w-[9.5rem]">
    <x-input label="to" name="to" type="date" wire:model.live="to" />
</div>
@if ($active)
    <button type="button" wire:click="clearDateRange" class="mb-2.5 text-xs font-medium text-accent-600 hover:underline">Clear dates</button>
@endif
