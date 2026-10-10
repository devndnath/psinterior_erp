<div>
    <x-breadcrumbs :items="['Reports' => route('reports.index'), 'Financial Summary' => null]" class="mb-4" />

    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <div class="flex items-end gap-3">
            <div>
                <label class="form-label">From</label>
                <input type="date" wire:model.live="dateFrom" class="block w-40 rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
            </div>
            <div>
                <label class="form-label">To</label>
                <input type="date" wire:model.live="dateTo" class="block w-40 rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
            </div>
        </div>
        <button type="button" wire:click="exportCsv" class="btn-secondary">Export CSV</button>
    </div>

    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="card p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Total Invoiced</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900">{{ number_format($totals['total_invoiced'], 2) }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Total Received</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900">{{ number_format($totals['total_received'], 2) }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Outstanding</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900">{{ number_format($totals['total_outstanding'], 2) }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Total Expenses</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900">{{ number_format($totals['total_expenses'], 2) }}</p>
        </div>
    </div>

    <div class="card p-6">
        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Invoice Ageing (outstanding amount, by days past due)</h2>
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            @foreach ($ageing as $bucket => $amount)
                <div class="rounded-lg border border-gray-100 p-4">
                    <p class="text-xs text-gray-500">{{ $bucket }} days</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900">{{ number_format($amount, 2) }}</p>
                </div>
            @endforeach
        </div>
    </div>
</div>
