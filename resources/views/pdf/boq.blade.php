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
    </style>
</head>
<body>
    @include('pdf._header', ['documentTitle' => 'Bill of Quantities', 'documentNo' => $boq->boq_no])

    <table style="margin-bottom: 16px;">
        <tr>
            <td><strong>Project:</strong> {{ $boq->project?->name }}</td>
            <td class="text-right"><strong>Version:</strong> {{ $boq->version }}</td>
        </tr>
        <tr>
            <td><strong>Client:</strong> {{ $boq->project?->client?->name }}</td>
            <td class="text-right"><strong>Status:</strong> {{ $boq->status->label() }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Category</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Rate</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($boq->items as $item)
                <tr>
                    <td>{{ $item->item }}{{ $item->description ? ' — '.$item->description : '' }}</td>
                    <td>{{ $item->category }}</td>
                    <td class="text-right">{{ $item->qty }} {{ $item->unit }}</td>
                    <td class="text-right">{{ number_format($item->rate, 2) }}</td>
                    <td class="text-right">{{ number_format($item->amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table style="width: 260px; margin-left: auto; margin-top: 10px;" class="totals">
        <tr><td class="label">Subtotal</td><td class="text-right">{{ number_format($boq->subtotal, 2) }}</td></tr>
        <tr><td class="label">Discount</td><td class="text-right">-{{ number_format($boq->discount, 2) }}</td></tr>
        <tr><td class="label">Tax</td><td class="text-right">+{{ number_format($boq->tax, 2) }}</td></tr>
        <tr><td><strong>Grand Total</strong></td><td class="text-right"><strong>{{ number_format($boq->grand_total, 2) }}</strong></td></tr>
    </table>
</body>
</html>
