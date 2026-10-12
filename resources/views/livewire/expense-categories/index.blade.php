<div>
    {{-- Phase 16: see designs/index.blade.php — replaced the hand-rolled
         <h1> (duplicated by layouts.admin's old fallback heading, now
         removed) with the standard <x-page-header>. --}}
    <x-page-header title="Expense Categories">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Expense Categories' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @can('create', \App\Models\ExpenseCategory::class)
                <a href="{{ route('expense-categories.create') }}" wire:navigate class="btn-primary">+ New Category</a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="card overflow-hidden">
        @if ($categories->isEmpty())
            <x-empty-state
                title="No expense categories found"
                description="Create a category to classify project expenses."
            />
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach ($categories as $category)
                        <tr wire:key="category-{{ $category->id }}" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $category->name }}</td>
                            <td class="px-4 py-3 text-sm">
                                <x-status-badge :label="$category->is_active ? 'Active' : 'Inactive'" :color="$category->is_active ? 'green' : 'gray'" />
                            </td>
                            <td class="px-4 py-3 text-right text-sm">
                                @can('update', $category)
                                    <a href="{{ route('expense-categories.edit', $category) }}" wire:navigate class="text-gray-500 hover:text-gray-700">Edit</a>
                                @endcan
                                @can('delete', $category)
                                    <span class="ml-3 inline-block">
                                        <x-confirm-dialog
                                            title="Delete this category?"
                                            :message='"This will remove {$category->name}. This cannot be undone."'
                                            action="deleteCategory"
                                            :params="[$category->id]"
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
                {{ $categories->links() }}
            </div>
        @endif
    </div>
</div>
