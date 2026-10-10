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
    @include('pdf._header', ['documentTitle' => 'Invoice', 'documentNo' => $invoice->invoice_no])

    <table style="margin-bottom: 16px;">
        <tr>
            <td><strong>Client:</strong> {{ $invoice->client?->name }}</td>
            <td class="text-right"><strong>Status:</strong> {{ $invoice->status->label() }}</td>
        </tr>
        <tr>
            <td><strong>Project:</strong> {{ $invoice->project?->name }}</td>
            <td class="text-right"><strong>Invoice Date:</strong> {{ $invoice->invoice_date?->format('d M, Y') }}</td>
        </tr>
        <tr>
            <td></td>
            <td class="text-right"><strong>Due Date:</strong> {{ $invoice->due_date?->format('d M, Y') ?? '—' }}</td>
        </tr>
    </table>

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
            @foreach ($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="text-right">{{ $item->qty }}</td>
                    <td class="text-right">{{ number_format($item->rate, 2) }}</td>
                    <td class="text-right">{{ number_format($item->amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table style="width: 260px; margin-left: auto; margin-top: 10px;" class="totals">
        <tr><td class="label">Subtotal</td><td class="text-right">{{ number_format($invoice->subtotal, 2) }}</td></tr>
        <tr><td class="label">Discount</td><td class="text-right">-{{ number_format($invoice->discount, 2) }}</td></tr>
        <tr><td class="label">Tax</td><td class="text-right">+{{ number_format($invoice->tax, 2) }}</td></tr>
        <tr><td><strong>Grand Total</strong></td><td class="text-right"><strong>{{ number_format($invoice->grand_total, 2) }}</strong></td></tr>
        <tr><td class="label">Paid</td><td class="text-right">{{ number_format($invoice->paid_amount, 2) }}</td></tr>
        <tr><td><strong>Balance Due</strong></td><td class="text-right"><strong>{{ number_format($invoice->outstandingAmount(), 2) }}</strong></td></tr>
    </table>

    @if ($invoice->notes)
        <div class="section-title">Notes</div>
        <p>{{ $invoice->notes }}</p>
    @endif
</body>
</html>
