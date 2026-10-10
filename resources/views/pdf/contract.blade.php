<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #111; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 6px 8px; text-align: left; }
        thead th { background: #f3f4f6; border-bottom: 1px solid #ddd; font-size: 11px; text-transform: uppercase; }
        tbody td { border-bottom: 1px solid #eee; }
        .text-right { text-align: right; }
        .totals td { border: none; padding: 3px 8px; }
        .totals .label { color: #555; }
        .section-title { font-size: 11px; text-transform: uppercase; color: #555; margin-top: 16px; margin-bottom: 6px; }
    </style>
</head>
<body>
    @include('pdf._header', ['documentTitle' => 'Contract', 'documentNo' => $contract->contract_no])

    <table style="margin-bottom: 16px;">
        <tr>
            <td><strong>Client:</strong> {{ $contract->client?->name }}</td>
            <td class="text-right"><strong>Status:</strong> {{ $contract->status->label() }}</td>
        </tr>
        <tr>
            <td><strong>Project:</strong> {{ $contract->project?->name }}</td>
            <td class="text-right"><strong>Amount:</strong> {{ number_format($contract->amount, 2) }}</td>
        </tr>
        <tr>
            <td><strong>Start Date:</strong> {{ $contract->start_date?->format('d M, Y') }}</td>
            <td class="text-right"><strong>End Date:</strong> {{ $contract->end_date?->format('d M, Y') ?? '—' }}</td>
        </tr>
    </table>

    @if ($contract->items->isNotEmpty())
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Rate</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($contract->items as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td class="text-right">{{ $item->qty }}</td>
                        <td class="text-right">{{ number_format($item->rate, 2) }}</td>
                        <td class="text-right">{{ number_format($item->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if ($contract->payment_terms)
        <div class="section-title">Payment Terms</div>
        <p>{{ $contract->payment_terms }}</p>
    @endif

    @if ($contract->warranty_terms)
        <div class="section-title">Warranty Terms</div>
        <p>{{ $contract->warranty_terms }}</p>
    @endif

    @if ($contract->terms)
        <div class="section-title">Terms &amp; Conditions</div>
        <p>{{ $contract->terms }}</p>
    @endif
</body>
</html>
