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
    @include('pdf._header', ['documentTitle' => 'Purchase Order', 'documentNo' => $purchaseOrder->po_no])

    <table style="margin-bottom: 16px;">
        <tr>
            <td><strong>Vendor:</strong> {{ $purchaseOrder->vendor?->company_name }}</td>
            <td class="text-right"><strong>Status:</strong> {{ $purchaseOrder->status->label() }}</td>
        </tr>
        <tr>
            <td><strong>Project:</strong> {{ $purchaseOrder->project?->name ?? '—' }}</td>
            <td class="text-right"><strong>Date:</strong> {{ $purchaseOrder->date?->format('d M, Y') }}</td>
        </tr>
        <tr>
            <td><strong>Warehouse:</strong> {{ $purchaseOrder->warehouse?->name }}</td>
            <td class="text-right"><strong>Expected Delivery:</strong> {{ $purchaseOrder->expected_delivery?->format('d M, Y') ?? '—' }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Rate</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($purchaseOrder->items as $item)
                <tr>
                    <td>{{ $item->product?->name }}</td>
                    <td class="text-right">{{ $item->qty }}</td>
                    <td class="text-right">{{ number_format($item->rate, 2) }}</td>
                    <td class="text-right">{{ number_format($item->amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table style="width: 260px; margin-left: auto; margin-top: 10px;" class="totals">
        <tr><td class="label">Subtotal</td><td class="text-right">{{ number_format($purchaseOrder->subtotal, 2) }}</td></tr>
        <tr><td class="label">Discount</td><td class="text-right">-{{ number_format($purchaseOrder->discount, 2) }}</td></tr>
        <tr><td class="label">Tax</td><td class="text-right">+{{ number_format($purchaseOrder->tax, 2) }}</td></tr>
        <tr><td><strong>Total</strong></td><td class="text-right"><strong>{{ number_format($purchaseOrder->total, 2) }}</strong></td></tr>
    </table>

    @if ($purchaseOrder->terms)
        <div class="section-title">Terms</div>
        <p>{{ $purchaseOrder->terms }}</p>
    @endif
</body>
</html>
