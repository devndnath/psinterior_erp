<div>
    <x-page-header :title="$vendorInvoice->vi_no">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Invoices' => route('vendor.invoices.index'), $vendorInvoice->vi_no => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            <x-status-badge :label="$vendorInvoice->status->label()" :color="$vendorInvoice->status->color()" />
        </x-slot:actions>
    </x-page-header>

    <p class="-mt-4 mb-6 text-sm text-ink-500">
        @if ($vendorInvoice->purchaseOrder)
            Against {{ $vendorInvoice->purchaseOrder->po_no }}
        @endif
    </p>

    <div class="panel p-6">
        <dl class="grid grid-cols-2 gap-4 text-sm">
            <div><dt class="text-ink-500">Your invoice no.</dt><dd class="font-medium text-ink-900">{{ $vendorInvoice->vendor_invoice_no ?: '—' }}</dd></div>
            <div><dt class="text-ink-500">Invoice date</dt><dd class="font-medium text-ink-900">{{ $vendorInvoice->invoice_date?->format('d M, Y') }}</dd></div>
            <div><dt class="text-ink-500">Amount</dt><dd class="font-semibold text-ink-900">{{ number_format($vendorInvoice->amount, 2) }}</dd></div>
            <div class="col-span-2"><dt class="text-ink-500">Notes</dt><dd class="whitespace-pre-line font-medium text-ink-900">{{ $vendorInvoice->notes ?: '—' }}</dd></div>
        </dl>
    </div>

    @if ($vendorInvoice->isReviewed())
        <div class="mt-6 panel p-6">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Review outcome</h2>
            <p class="whitespace-pre-line text-sm text-ink-900">{{ $vendorInvoice->review_notes ?: 'No additional notes.' }}</p>
        </div>
    @endif

    @if ($files->isNotEmpty())
        <div class="mt-6 panel p-6">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Attachments</h2>
            <ul class="space-y-2 text-sm">
                @foreach ($files as $file)
                    <li><a href="{{ $file->url() }}" target="_blank" class="text-accent-600 hover:underline">{{ $file->file_name }}</a></li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
