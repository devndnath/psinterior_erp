{{--
    Select input with label + Livewire validation error, same convention
    as <x-input> (see that file's docblock). Pass <option> tags as the
    slot so the caller keeps control of the option list.

    <x-select label="Status" name="status" wire:model="status">
        <option value="">All</option>
        @foreach ($statuses as $value => $label)
            <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
    </x-select>
--}}
@props(['label' => null, 'name', 'error' => null])

<div>
    @if ($label)
        <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    @endif

    <select
        id="{{ $name }}"
        name="{{ $name }}"
        {{ $attributes->merge(['class' => 'field-input']) }}
    >
        {{ $slot }}
    </select>

    @php($message = $error ?? ($errors->first($name) ?: null))
    @if ($message)
        <p class="form-error">{{ $message }}</p>
    @endif
</div>
