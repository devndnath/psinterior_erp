{{--
    Textarea with label + Livewire validation error, same convention as
    <x-input> (see that file's docblock).

    <x-textarea label="Notes" name="notes" rows="3" wire:model="notes" />
--}}
@props(['label' => null, 'name', 'error' => null])

<div>
    @if ($label)
        <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    @endif

    <textarea
        id="{{ $name }}"
        name="{{ $name }}"
        {{ $attributes->merge(['class' => 'field-input']) }}
    >{{ $slot }}</textarea>

    @php($message = $error ?? ($errors->first($name) ?: null))
    @if ($message)
        <p class="form-error">{{ $message }}</p>
    @endif
</div>
