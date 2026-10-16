<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #111; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 6px 8px; text-align: left; }
        .text-right { text-align: right; }
        .totals td { border: none; padding: 3px 8px; }
        .totals .label { color: #555; }
        .section-title { font-size: 11px; text-transform: uppercase; color: #555; margin-top: 16px; margin-bottom: 6px; }
    </style>
</head>
<body>
    @include('pdf._header', ['documentTitle' => 'Payment Receipt', 'documentNo' => $payment->payment_no])

    <table style="margin-bottom: 16px;">
        <tr>
            <td><strong>Client:</strong> {{ $payment->client?->name ?? '—' }}</td>
            <td class="text-right"><strong>Date:</strong> {{ $payment->date?->format('d M, Y') }}</td>
        </tr>
        <tr>
            <td><strong>Project:</strong> {{ $payment->project?->name ?? '—' }}</td>
            <td class="text-right"><strong>Method:</strong> {{ $payment->method->label() }}</td>
        </tr>
        <tr>
            <td><strong>Invoice:</strong> {{ $payment->invoice?->invoice_no ?? '—' }}</td>
            <td class="text-right"><strong>Reference:</strong> {{ $payment->reference ?: '—' }}</td>
        </tr>
        <tr>
            <td></td>
            <td class="text-right"><strong>Account:</strong> {{ $payment->account ?: '—' }}</td>
        </tr>
    </table>

    <table style="width: 260px; margin-left: auto; margin-top: 10px;" class="totals">
        <tr><td><strong>Amount Received</strong></td><td class="text-right"><strong>{{ number_format($payment->amount, 2) }}</strong></td></tr>
    </table>

    @if ($payment->notes)
        <div class="section-title">Notes</div>
        <p>{{ $payment->notes }}</p>
    @endif
</body>
</html>
