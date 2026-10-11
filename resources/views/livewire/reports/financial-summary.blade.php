<div>
    <x-breadcrumbs :items="['Reports' => route('reports.index'), 'Financial Summary' => null]" class="mb-4" />

    <div class="panel mb-4 flex flex-wrap items-end justify-between gap-4 p-4">
        <div class="flex flex-wrap items-end gap-4">
            <x-input label="From" name="dateFrom" type="date" wire:model.live="dateFrom" class="w-40" />
            <x-input label="To" name="dateTo" type="date" wire:model.live="dateTo" class="w-40" />
        </div>
        <x-button variant="secondary" wire:click="exportCsv">Export CSV</x-button>
    </div>

    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="panel p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-ink-500">Total Invoiced</p>
            <p class="mt-1 text-2xl font-semibold text-ink-900">{{ number_format($totals['total_invoiced'], 2) }}</p>
        </div>
        <div class="panel p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-ink-500">Total Received</p>
            <p class="mt-1 text-2xl font-semibold text-ink-900">{{ number_format($totals['total_received'], 2) }}</p>
        </div>
        <div class="panel p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-ink-500">Outstanding</p>
            <p class="mt-1 text-2xl font-semibold text-ink-900">{{ number_format($totals['total_outstanding'], 2) }}</p>
        </div>
        <div class="panel p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-ink-500">Total Expenses</p>
            <p class="mt-1 text-2xl font-semibold text-ink-900">{{ number_format($totals['total_expenses'], 2) }}</p>
        </div>
    </div>

    <div class="panel p-6">
        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Invoice Ageing (outstanding amount, by days past due)</h2>
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            @foreach ($ageing as $bucket => $amount)
                <div class="rounded-lg border border-ink-100 p-4">
                    <p class="text-xs text-ink-500">{{ $bucket }} days</p>
                    <p class="mt-1 text-lg font-semibold text-ink-900">{{ number_format($amount, 2) }}</p>
                </div>
            @endforeach
        </div>
    </div>
</div>
