{{--
    Text input with label + Livewire validation error, matching the
    convention already used by hand-rolled form markup app-wide (see
    resources/views/livewire/leads/form.blade.php): wire:model on the
    <input>, @error('field') ... @enderror rendered as a .form-error <p>
    right below it.

    <x-input label="Name" name="name" wire:model="name" />
    <x-input label="Budget" name="estimated_budget" type="number" step="0.01" wire:model="estimated_budget" />
--}}
@props(['label' => null, 'name', 'type' => 'text', 'error' => null])

<div>
    @if ($label)
        <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    @endif

    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        {{ $attributes->merge(['class' => 'field-input']) }}
    >

    @php($message = $error ?? ($errors->first($name) ?: null))
    @if ($message)
        <p class="form-error">{{ $message }}</p>
    @endif
</div>
