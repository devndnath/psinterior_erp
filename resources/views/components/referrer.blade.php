{{--
    "Referred by" value for a Lead or Client (HasReference): the referrer's
    name — linked when it's an existing client — with the kind beneath.
--}}
@props(['model'])

@if ($model->hasReference())
    <span {{ $attributes }}>
        @if ($model->reference_type === 'client' && $model->referenceClient)
            <a href="{{ route('clients.show', $model->referenceClient) }}" wire:navigate class="font-medium text-accent-600 hover:underline">{{ $model->referenceClient->name }}</a>
        @else
            <span class="font-medium text-ink-900">{{ $model->referrerName() ?? '—' }}</span>
        @endif
        <span class="block text-xs font-normal text-ink-500">{{ $model->referrerKind() }}{{ $model->reference_type === 'other' && $model->reference_phone ? ' · '.$model->reference_phone : '' }}</span>
    </span>
@else
    <span {{ $attributes->merge(['class' => 'font-medium text-ink-400']) }}>—</span>
@endif
