{{--
    Shared PDF header partial — company name/address/contact pulled from
    Settings (App\Models\Setting::get(), the same static helper every
    staff screen uses). Included by every resources/views/pdf/*.blade.php
    template. Plain Blade, no Livewire — DomPDF renders static HTML.
--}}
<table style="width: 100%; margin-bottom: 20px;">
    <tr>
        <td style="vertical-align: top;">
            <div style="font-size: 18px; font-weight: bold;">{{ \App\Models\Setting::get('company.name', config('app.name')) }}</div>
            @if ($companyAddress = \App\Models\Setting::get('company.address'))
                <div style="font-size: 11px; color: #555;">{{ $companyAddress }}</div>
            @endif
            <div style="font-size: 11px; color: #555;">
                @if ($companyPhone = \App\Models\Setting::get('company.phone'))
                    {{ $companyPhone }}
                @endif
                @if ($companyEmail = \App\Models\Setting::get('company.email'))
                    &nbsp;&middot;&nbsp;{{ $companyEmail }}
                @endif
            </div>
        </td>
        <td style="vertical-align: top; text-align: right;">
            <div style="font-size: 16px; font-weight: bold; text-transform: uppercase;">{{ $documentTitle }}</div>
            <div style="font-size: 12px; color: #555;">{{ $documentNo }}</div>
        </td>
    </tr>
</table>
<hr style="border: 0; border-top: 1px solid #ccc; margin-bottom: 16px;">
