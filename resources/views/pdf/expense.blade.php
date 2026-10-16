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
    @include('pdf._header', ['documentTitle' => 'Expense Voucher', 'documentNo' => $expense->expense_no])

    <table style="margin-bottom: 16px;">
        <tr>
            <td><strong>Project:</strong> {{ $expense->project?->name ?? '—' }}</td>
            <td class="text-right"><strong>Date:</strong> {{ $expense->date?->format('d M, Y') }}</td>
        </tr>
        <tr>
            <td><strong>Category:</strong> {{ $expense->category?->name ?? '—' }}</td>
            <td class="text-right"><strong>Method:</strong> {{ $expense->method->label() }}</td>
        </tr>
        <tr>
            <td><strong>Vendor:</strong> {{ $expense->vendor?->name ?? $expense->vendor_name ?: '—' }}</td>
            <td class="text-right"><strong>Recorded by:</strong> {{ $expense->createdBy?->name ?? '—' }}</td>
        </tr>
    </table>

    <table style="width: 260px; margin-left: auto; margin-top: 10px;" class="totals">
        <tr><td><strong>Amount</strong></td><td class="text-right"><strong>{{ number_format($expense->amount, 2) }}</strong></td></tr>
    </table>

    @if ($expense->description)
        <div class="section-title">Description</div>
        <p>{{ $expense->description }}</p>
    @endif
</body>
</html>
