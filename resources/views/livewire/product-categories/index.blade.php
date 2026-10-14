<div>
    <x-page-header title="Product Categories" description="Classify products into categories for filtering and reporting.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Product Categories' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @can('create', \App\Models\ProductCategory::class)
                <x-button href="{{ route('product-categories.create') }}" wire:navigate>+ New Category</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    @if ($categories->isEmpty())
        <div class="panel overflow-hidden">
            <x-empty-state
                title="No product categories found"
                description="Create a category to classify products."
            />
        </div>
    @else
        <x-table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($categories as $category)
                    <tr wire:key="category-{{ $category->id }}">
                        <td class="font-medium text-ink-900">{{ $category->name }}</td>
                        <td>
                            <x-status-badge :label="$category->is_active ? 'Active' : 'Inactive'" :color="$category->is_active ? 'green' : 'gray'" />
                        </td>
                        <td class="text-right">
                            {{-- Phase 5 (premium-SaaS redesign): a single "..." action menu instead of several separate text links in every row. --}}
                            <x-dropdown>
                            @can('update', $category)
                                <a href="{{ route('product-categories.edit', $category) }}" wire:navigate class="dropdown-item">Edit</a>
                            @endcan
                            @can('delete', $category)
                                                                    <x-confirm-dialog
                                        title="Delete this category?"
                                        :message='"This will remove {$category->name}. This cannot be undone."'
                                        action="deleteCategory"
                                        :params="[$category->id]"
                                    >
                                        <x-slot:trigger>
                                            <button type="button" class="dropdown-item-danger">Delete</button>
                                        </x-slot:trigger>
                                    </x-confirm-dialog>
                            @endcan
                            </x-dropdown>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>
        <div class="mt-4">
            {{ $categories->links() }}
        </div>
    @endif
</div>
