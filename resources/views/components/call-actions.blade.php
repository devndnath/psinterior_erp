{{--
    One-tap Call (tel:) and WhatsApp (wa.me) buttons for a phone number —
    the same pair on the Calls page, the CRM overview and the lead/client
    call cards. Renders nothing without a number.

    <x-call-actions :phone="$client->phone" />            icon buttons
    <x-call-actions :phone="$lead->phone" labelled />      buttons with text
--}}
@props(['phone' => null, 'labelled' => false])

@php
    $tel = \App\Models\LeadFollowup::telUrl($phone);
    $wa = \App\Models\LeadFollowup::whatsappUrl($phone);
@endphp

@if ($tel)
    <span {{ $attributes->merge(['class' => 'inline-flex shrink-0 items-center gap-1.5']) }}>
        <a href="{{ $tel }}" title="Call {{ $phone }}" @class([
            'inline-flex items-center justify-center gap-1.5 rounded-full bg-success-600 font-semibold text-white transition hover:bg-success-700',
            'h-8 w-8' => ! $labelled,
            'px-3.5 py-2 text-xs' => $labelled,
        ])>
            <x-icon name="phone" class="h-3.5 w-3.5" />@if ($labelled)<span>Call</span>@endif
        </a>
        <a href="{{ $wa }}" target="_blank" rel="noopener" title="WhatsApp {{ $phone }}" @class([
            'inline-flex items-center justify-center gap-1.5 rounded-full border border-success-500/40 bg-success-50 font-semibold text-success-700 transition hover:bg-success-100 dark:bg-success-500/10 dark:text-success-400',
            'h-8 w-8' => ! $labelled,
            'px-3.5 py-2 text-xs' => $labelled,
        ])>
            <x-icon name="message-circle" class="h-3.5 w-3.5" />@if ($labelled)<span>WhatsApp</span>@endif
        </a>
    </span>
@endif
