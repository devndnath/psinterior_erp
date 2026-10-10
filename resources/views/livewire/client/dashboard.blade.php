<div>
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="card p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Active Projects</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $activeProjects }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Designs Awaiting Decision</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $pendingDesignApprovals }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Quotations Awaiting Decision</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $pendingQuotationApprovals }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Outstanding Balance</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900">{{ number_format($outstandingInvoiceTotal, 2) }}</p>
        </div>
    </div>
</div>
