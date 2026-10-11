<div>
    <x-page-header title="Invoices" description="Bill clients for work done on a project and track what's outstanding.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Invoices' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @can('create', \App\Models\Invoice::class)
                <x-button href="{{ route('invoices.create') }}" wire:navigate>+ New Invoice</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="panel mb-4 flex flex-wrap items-end gap-4 p-4">
        <div class="min-w-[220px] flex-1">
            <x-input label="Search" name="search" wire:model.live.debounce.400ms="search" placeholder="Invoice no…" />
        </div>
        <div>
            <x-select label="Project" name="project" wire:model.live="project">
                <option value="">Any project</option>
                @foreach ($projects as $proj)
                    <option value="{{ $proj->id }}">{{ $proj->project_code }} — {{ $proj->name }}</option>
                @endforeach
            </x-select>
        </div>
        <div>
            <x-select label="Type" name="type" wire:model.live="type">
                <option value="">Any type</option>
                @foreach ($types as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-select>
        </div>
        <div>
            <x-select label="Status" name="status" wire:model.live="status">
                <option value="">Any status</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-select>
        </div>
        <x-button variant="secondary" wire:click="resetFilters">Reset</x-button>
    </div>

    @if ($invoices->isEmpty())
        <div class="panel overflow-hidden">
            <x-empty-state
                title="No invoices found"
                description="Create an invoice to bill a client for work done on a project."
            />
        </div>
    @else
        <x-table>
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Project</th>
                    <th>Type</th>
                    <th>Grand Total</th>
                    <th>Outstanding</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoices as $invoice)
                    <tr wire:key="invoice-{{ $invoice->id }}">
                        <td class="font-mono">
                            <a href="{{ route('invoices.show', $invoice) }}" wire:navigate class="text-accent-600 hover:underline">{{ $invoice->invoice_no }}</a>
                        </td>
                        <td>
                            @if ($invoice->project)
                                <a href="{{ route('projects.show', $invoice->project) }}" wire:navigate class="hover:underline">{{ $invoice->project->project_code }}</a>
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $invoice->type->label() }}</td>
                        <td class="font-medium text-ink-900">{{ number_format($invoice->grand_total, 2) }}</td>
                        <td>{{ number_format($invoice->outstandingAmount(), 2) }}</td>
                        <td>
                            <x-status-badge :label="$invoice->status->label()" :color="$invoice->status->color()" />
                        </td>
                        <td class="text-right">
                            <a href="{{ route('invoices.show', $invoice) }}" wire:navigate class="text-ink-500 hover:text-ink-700">View</a>
                            @can('update', $invoice)
                                <a href="{{ route('invoices.edit', $invoice) }}" wire:navigate class="ml-3 text-ink-500 hover:text-ink-700">Edit</a>
                            @endcan
                            @can('delete', $invoice)
                                <span class="ml-3 inline-block">
                                    <x-confirm-dialog
                                        title="Delete this invoice?"
                                        :message='"This will remove {$invoice->invoice_no} and its items. This cannot be undone."'
                                        action="deleteInvoice"
                                        :params="[$invoice->id]"
                                    >
                                        <x-slot:trigger>
                                            <button type="button" class="text-danger-600 hover:text-danger-700">Delete</button>
                                        </x-slot:trigger>
                                    </x-confirm-dialog>
                                </span>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>
        <div class="mt-4">
            {{ $invoices->links() }}
        </div>
    @endif
</div>
