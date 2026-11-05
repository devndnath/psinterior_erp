<div>
    <x-page-header title="Vendors" description="Manage the vendor directory used across purchase requests, quotations and purchase orders.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Vendors' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @can('create', \App\Models\Vendor::class)
                <x-button href="{{ route('vendors.create') }}" wire:navigate>+ New Vendor</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    {{-- What we owe vendors, across every vendor matching the filters.
         Payable = purchase orders placed (not drafts/cancelled); Paid =
         expenses against the vendor; Due = payable − paid. --}}
    @if ($summary)
        @php $currency = \App\Models\Setting::get('finance.currency', 'BDT'); @endphp
        <div class="mb-4 grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
            <div class="panel p-4 sm:p-5">
                <p class="text-xs font-medium text-ink-500">Total payable</p>
                <p class="mt-2 flex items-baseline gap-1 truncate"><span class="text-xs font-semibold text-ink-400">{{ $currency }}</span><span class="text-xl font-bold tracking-tight tabular-nums text-ink-900 sm:text-2xl">{{ number_format($summary['payable'], 2) }}</span></p>
                <p class="mt-1 truncate text-[11px] text-ink-500">{{ number_format($summary['vendors']) }} {{ \Illuminate\Support\Str::plural('vendor', $summary['vendors']) }} · purchase orders placed</p>
            </div>
            <div class="panel p-4 sm:p-5">
                <p class="flex items-center gap-1.5 text-xs font-medium text-ink-500"><span class="h-2 w-2 rounded-full bg-[#059669] dark:bg-[#12A372]"></span>Paid</p>
                <p class="mt-2 flex items-baseline gap-1 truncate"><span class="text-xs font-semibold text-ink-400">{{ $currency }}</span><span class="text-xl font-bold tracking-tight tabular-nums text-ink-900 sm:text-2xl">{{ number_format($summary['paid'], 2) }}</span></p>
                <p class="mt-1 truncate text-[11px] text-ink-500">{{ $summary['payable'] > 0 ? round($summary['paid'] / $summary['payable'] * 100).'% of payable' : 'paid to vendors' }}</p>
            </div>
            <button type="button" wire:click="$set('balance', 'due')" class="rounded-3xl bg-charcoal p-4 text-left text-white transition hover:-translate-y-0.5 sm:p-5 dark:bg-white/[0.06]">
                <p class="text-xs font-medium text-white/70">Due to vendors</p>
                <p class="mt-2 flex items-baseline gap-1 truncate"><span class="text-xs font-semibold text-white/60">{{ $currency }}</span><span class="text-xl font-bold tracking-tight tabular-nums text-accent-400 sm:text-2xl">{{ number_format($summary['due'], 2) }}</span></p>
                <p class="mt-1 truncate text-[11px] text-white/60">{{ $summary['withDue'] }} {{ \Illuminate\Support\Str::plural('vendor', $summary['withDue']) }} still to pay · tap to list them</p>
            </button>
            <div class="panel p-4 sm:p-5">
                <p class="text-xs font-medium text-ink-500">Advance paid</p>
                <p class="mt-2 flex items-baseline gap-1 truncate"><span class="text-xs font-semibold text-ink-400">{{ $currency }}</span><span class="text-xl font-bold tracking-tight tabular-nums text-ink-900 sm:text-2xl">{{ number_format($summary['advance'], 2) }}</span></p>
                <p class="mt-1 truncate text-[11px] text-ink-500">paid ahead of orders</p>
            </div>
        </div>
    @endif

    <div class="panel mb-4 flex flex-wrap items-end gap-4 p-4">
        <div class="min-w-[220px] flex-1">
            <x-input label="Search" name="search" wire:model.live.debounce.400ms="search" placeholder="Company, code or contact…" />
        </div>
        @if ($money)
            <div>
                <x-select label="Payment status" name="balance" wire:model.live="balance">
                    <option value="">All vendors</option>
                    <option value="due">Has due</option>
                    <option value="paid">Fully paid</option>
                    <option value="advance">Advance paid</option>
                    <option value="none">No purchases yet</option>
                </x-select>
            </div>
            <div>
                <x-select label="Sort by" name="sort" wire:model.live="sort">
                    <option value="latest">Newest first</option>
                    <option value="name">Name (A–Z)</option>
                    <option value="due">Highest due</option>
                    <option value="payable">Highest payable</option>
                    <option value="paid">Highest paid</option>
                </x-select>
            </div>
        @endif
        <x-button variant="secondary" wire:click="resetFilters">Reset</x-button>
    </div>

    @if ($vendors->isEmpty())
        <div class="panel overflow-hidden">
            <x-empty-state
                title="No vendors found"
                description="Add a vendor to start the procurement pipeline — purchase requests, quotations and purchase orders."
            />
        </div>
    @else
        <x-table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Company</th>
                    <th>Category</th>
                    <th>Contact</th>
                    @if ($money)
                        <th class="!text-right">Payable</th>
                        <th class="!text-right">Paid</th>
                        <th class="!text-right">Due</th>
                    @endif
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($vendors as $vendor)
                    <tr wire:key="vendor-{{ $vendor->id }}">
                        <td class="font-mono">
                            <a href="{{ route('vendors.show', $vendor) }}" wire:navigate class="text-accent-600 hover:underline">{{ $vendor->vendor_code }}</a>
                        </td>
                        <td class="font-medium text-ink-900">{{ $vendor->company_name }}</td>
                        <td>{{ $vendor->category ?: '—' }}</td>
                        <td>
                            <span class="block">{{ $vendor->contact_person ?: '—' }}</span>
                            @if ($vendor->phone)
                                <span class="block text-xs text-ink-500">{{ $vendor->phone }}</span>
                            @endif
                        </td>
                        @if ($money)
                            @php
                                $payable = (float) $vendor->payable_sum;
                                $paid = (float) $vendor->paid_sum;
                                $due = $payable - $paid;
                            @endphp
                            <td class="whitespace-nowrap text-right tabular-nums text-ink-900">{{ number_format($payable, 2) }}</td>
                            <td class="whitespace-nowrap text-right tabular-nums text-ink-700">{{ number_format($paid, 2) }}</td>
                            <td class="whitespace-nowrap text-right tabular-nums">
                                @if ($due > 0.005)
                                    <span class="font-semibold text-danger-600">{{ number_format($due, 2) }}</span>
                                    <span class="block text-[11px] text-ink-500">{{ $payable > 0 ? round($paid / $payable * 100).'% paid' : '' }}</span>
                                @elseif ($due < -0.005)
                                    <span class="font-semibold text-ink-900">{{ number_format(-$due, 2) }}</span>
                                    <span class="block text-[11px] text-accent-700 dark:text-accent-400">Advance</span>
                                @elseif ($payable > 0)
                                    <span class="rounded-full bg-success-50 px-2.5 py-1 text-[11px] font-semibold text-success-700 dark:bg-success-500/10 dark:text-success-400">Paid</span>
                                @else
                                    <span class="text-ink-400">—</span>
                                @endif
                            </td>
                        @endif
                        <td class="text-right">
                            {{-- Phase 5 (premium-SaaS redesign): a single "..." action menu instead of several separate text links in every row. --}}
                            <x-dropdown>
                            <a href="{{ route('vendors.show', $vendor) }}" wire:navigate class="dropdown-item">View</a>
                            @can('update', $vendor)
                                <a href="{{ route('vendors.edit', $vendor) }}" wire:navigate class="dropdown-item">Edit</a>
                            @endcan
                            @can('delete', $vendor)
                                                                    <x-confirm-dialog
                                        title="Delete this vendor?"
                                        :message='"This will remove {$vendor->company_name} from the directory. This cannot be undone."'
                                        action="deleteVendor"
                                        :params="[$vendor->id]"
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
            {{ $vendors->links() }}
        </div>
    @endif
</div>
