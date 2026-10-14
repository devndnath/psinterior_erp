<div>
    <x-breadcrumbs :items="['Expenses' => route('expenses.index'), $expense->expense_no => null]" class="mb-4" />

    <div class="mb-6 flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">{{ $expense->expense_no }}</h1>
            <p class="mt-1 text-sm text-gray-500">
                @if ($expense->project)
                    <a href="{{ route('projects.show', $expense->project) }}" wire:navigate class="text-brand-600 hover:underline">{{ $expense->project->project_code }}</a>
                    — {{ $expense->project->name }}
                @endif
            </p>
        </div>
        <div class="flex items-center gap-3">
            @can('update', $expense)
                <a href="{{ route('expenses.edit', $expense) }}" wire:navigate class="btn-secondary">Edit</a>
            @endcan
            @can('delete', $expense)
                <x-confirm-dialog
                    title="Delete this expense?"
                    :message='"This will remove {$expense->expense_no}. This cannot be undone."'
                    action="deleteExpense"
                >
                    <x-slot:trigger>
                        <button type="button" class="btn-secondary text-red-600">Delete</button>
                    </x-slot:trigger>
                </x-confirm-dialog>
            @endcan
        </div>
    </div>

    <div class="max-w-xl card p-6">
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div><dt class="text-gray-500">Amount</dt><dd class="text-lg font-semibold text-gray-900">{{ number_format($expense->amount, 2) }}</dd></div>
            <div><dt class="text-gray-500">Date</dt><dd class="font-medium text-gray-900">{{ $expense->date?->format('d M, Y') }}</dd></div>
            <div><dt class="text-gray-500">Category</dt><dd class="font-medium text-gray-900">{{ $expense->category?->name ?? '—' }}</dd></div>
            <div><dt class="text-gray-500">Method</dt><dd class="font-medium text-gray-900">{{ $expense->method->label() }}</dd></div>
            <div><dt class="text-gray-500">Vendor</dt><dd class="font-medium text-gray-900">{{ $expense->vendor_name ?: '—' }}</dd></div>
            <div><dt class="text-gray-500">Recorded by</dt><dd class="font-medium text-gray-900">{{ $expense->createdBy?->name ?? '—' }}</dd></div>
            <div class="col-span-2"><dt class="text-gray-500">Description</dt><dd class="whitespace-pre-line font-medium text-gray-900">{{ $expense->description ?: '—' }}</dd></div>
        </dl>
    </div>
</div>
