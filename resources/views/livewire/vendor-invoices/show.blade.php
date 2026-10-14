<div>
    <x-page-header :title="$vendorInvoice->vi_no">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Vendor Invoices' => route('vendor-invoices.index'), $vendorInvoice->vi_no => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            <x-status-badge :label="$vendorInvoice->status->label()" :color="$vendorInvoice->status->color()" />
            @can('review', $vendorInvoice)
                <x-button wire:click="toggleReviewForm">{{ $showReviewForm ? 'Cancel' : 'Review' }}</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <p class="-mt-4 mb-6 text-sm text-ink-500">
        {{ $vendorInvoice->vendor?->company_name }}
        @if ($vendorInvoice->purchaseOrder)
            — <a href="{{ route('purchase-orders.show', $vendorInvoice->purchaseOrder) }}" wire:navigate class="text-accent-600 hover:underline">{{ $vendorInvoice->purchaseOrder->po_no }}</a>
        @endif
    </p>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Invoice details</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-ink-500">Vendor's invoice no.</dt><dd class="font-medium text-ink-900">{{ $vendorInvoice->vendor_invoice_no ?: '—' }}</dd></div>
                    <div><dt class="text-ink-500">Invoice date</dt><dd class="font-medium text-ink-900">{{ $vendorInvoice->invoice_date?->format('d M, Y') }}</dd></div>
                    <div><dt class="text-ink-500">Amount</dt><dd class="font-semibold text-ink-900">{{ number_format($vendorInvoice->amount, 2) }}</dd></div>
                    <div class="col-span-2"><dt class="text-ink-500">Notes from vendor</dt><dd class="whitespace-pre-line font-medium text-ink-900">{{ $vendorInvoice->notes ?: '—' }}</dd></div>
                </dl>
            </div>

            @if ($showReviewForm)
                <div class="panel p-6">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Review</h2>
                    <form wire:submit="approve" class="space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <x-select label="Expense category *" name="categoryId" wire:model="categoryId">
                                <option value="">Select…</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </x-select>
                            <x-select label="Payment method *" name="method" wire:model="method">
                                @foreach ($methods as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </x-select>
                        </div>
                        <x-textarea label="Review notes" name="reviewNotes" rows="2" wire:model="reviewNotes" />
                        <div class="flex items-center gap-3">
                            <x-button type="submit">Approve &amp; post to expenses</x-button>
                            <x-button type="button" variant="secondary" wire:click="reject" class="text-danger-600">Reject</x-button>
                        </div>
                        <p class="text-xs text-ink-400">Rejecting requires the review notes above to explain why.</p>
                    </form>
                </div>
            @endif

            @if ($vendorInvoice->isReviewed())
                <div class="panel p-6">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Review outcome</h2>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div><dt class="text-ink-500">Reviewed by</dt><dd class="font-medium text-ink-900">{{ $vendorInvoice->reviewer?->name ?? '—' }}</dd></div>
                        <div><dt class="text-ink-500">Reviewed at</dt><dd class="font-medium text-ink-900">{{ $vendorInvoice->reviewed_at?->format('d M, Y H:i') ?? '—' }}</dd></div>
                        @if ($vendorInvoice->expense)
                            <div class="col-span-2"><dt class="text-ink-500">Posted expense</dt><dd class="font-medium text-ink-900">{{ $vendorInvoice->expense->expense_no }} ({{ $vendorInvoice->expense->category?->name }})</dd></div>
                        @endif
                        <div class="col-span-2"><dt class="text-ink-500">Review notes</dt><dd class="whitespace-pre-line font-medium text-ink-900">{{ $vendorInvoice->review_notes ?: '—' }}</dd></div>
                    </dl>
                </div>
            @endif
        </div>

        <div>
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Attachments</h2>
                @if ($photos->isEmpty())
                    <p class="text-sm text-ink-500">No files attached.</p>
                @else
                    <ul class="space-y-2 text-sm">
                        @foreach ($photos as $file)
                            <li>
                                <a href="{{ $file->url() }}" target="_blank" class="text-accent-600 hover:underline">{{ $file->file_name }}</a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>
