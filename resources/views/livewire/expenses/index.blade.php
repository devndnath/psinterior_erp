<div>
    {{-- Phase 16: see designs/index.blade.php — replaced the hand-rolled
         <h1> (duplicated by layouts.admin's old fallback heading, now
         removed) with the standard <x-page-header>. --}}
    <x-page-header title="Expenses">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Expenses' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @can('create', \App\Models\Expense::class)
                <a href="{{ route('expenses.create') }}" wire:navigate class="btn-primary">+ New Expense</a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="card mb-4 flex flex-wrap items-end gap-4 p-4">
        <div class="min-w-[220px] flex-1">
            <label class="form-label">Search</label>
            <input wire:model.live.debounce.400ms="search" type="text" placeholder="Expense no…" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
        </div>
        <div>
            <label class="form-label">Project</label>
            <select wire:model.live="project" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                <option value="">Any project</option>
                @foreach ($projects as $proj)
                    <option value="{{ $proj->id }}">{{ $proj->project_code }} — {{ $proj->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Category</label>
            <select wire:model.live="category" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                <option value="">Any category</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="button" wire:click="resetFilters" class="btn-secondary">Reset</button>
    </div>

    <div class="card overflow-hidden">
        @if ($expenses->isEmpty())
            <x-empty-state
                title="No expenses found"
                description="Record an expense to track money spent on a project."
            />
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Expense #</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Project</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Category</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Amount</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach ($expenses as $expense)
                        <tr wire:key="expense-{{ $expense->id }}" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-mono text-gray-500">
                                <a href="{{ route('expenses.show', $expense) }}" wire:navigate class="text-brand-600 hover:underline">{{ $expense->expense_no }}</a>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                @if ($expense->project)
                                    <a href="{{ route('projects.show', $expense->project) }}" wire:navigate class="hover:underline">{{ $expense->project->project_code }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $expense->category?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $expense->date?->format('d M, Y') }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ number_format($expense->amount, 2) }}</td>
                            <td class="px-4 py-3 text-right text-sm">
                                <a href="{{ route('expenses.show', $expense) }}" wire:navigate class="text-gray-500 hover:text-gray-700">View</a>
                                @can('update', $expense)
                                    <a href="{{ route('expenses.edit', $expense) }}" wire:navigate class="ml-3 text-gray-500 hover:text-gray-700">Edit</a>
                                @endcan
                                @can('delete', $expense)
                                    <span class="ml-3 inline-block">
                                        <x-confirm-dialog
                                            title="Delete this expense?"
                                            :message='"This will remove {$expense->expense_no}. This cannot be undone."'
                                            action="deleteExpense"
                                            :params="[$expense->id]"
                                        >
                                            <x-slot:trigger>
                                                <button type="button" class="text-red-500 hover:text-red-700">Delete</button>
                                            </x-slot:trigger>
                                        </x-confirm-dialog>
                                    </span>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="border-t border-gray-100 px-4 py-3">
                {{ $expenses->links() }}
            </div>
        @endif
    </div>
</div>
