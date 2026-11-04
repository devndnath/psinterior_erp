<div>
    {{-- Phase 16: replaced the hand-rolled <h1> (duplicated by
         layouts.admin's old fallback heading, now removed) with the
         same <x-page-header> every other redesigned Index view uses. --}}
    <x-page-header title="Designs">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Designs' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @can('create', \App\Models\Design::class)
                <a href="{{ route('designs.create', ['for' => 'sold']) }}" wire:navigate class="btn-secondary">+ Sold Design</a>
                <a href="{{ route('designs.create') }}" wire:navigate class="btn-primary">+ New Design</a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    {{-- Design work at a glance: design sales money (when the user can
         see invoices) and where the designs stand. --}}
    @php $currency = \App\Models\Setting::get('finance.currency', 'BDT'); @endphp
    <div class="mb-4 grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
        @if ($summary['sales'])
            @foreach ([
                ['label' => 'Design Sales', 'value' => $summary['sales']['invoiced'], 'icon' => 'palette', 'note' => number_format($summary['sales']['count']).' design sale '.\Illuminate\Support\Str::plural('invoice', $summary['sales']['count']), 'tone' => 'ink'],
                ['label' => 'Received', 'value' => $summary['sales']['received'], 'icon' => 'arrow-down-right', 'note' => $summary['sales']['invoiced'] > 0 ? round($summary['sales']['received'] / $summary['sales']['invoiced'] * 100).'% collected' : 'from design sales', 'tone' => 'green'],
                ['label' => 'Due', 'value' => $summary['sales']['due'], 'icon' => 'receipt', 'note' => 'still to collect', 'tone' => 'dark'],
            ] as $card)
                <div @class([
                    'rounded-3xl p-4 sm:p-5',
                    'bg-charcoal text-white dark:bg-white/[0.06]' => $card['tone'] === 'dark',
                    'panel' => $card['tone'] !== 'dark',
                ])>
                    <div class="flex items-center justify-between gap-2">
                        <p @class(['text-xs font-medium', 'text-white/70' => $card['tone'] === 'dark', 'text-ink-500' => $card['tone'] !== 'dark'])>{{ $card['label'] }}</p>
                        <span @class([
                            'flex h-8 w-8 items-center justify-center rounded-full',
                            'bg-white/10 text-accent-400' => $card['tone'] === 'dark',
                            'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400' => $card['tone'] === 'green',
                            'bg-ink-100 text-ink-600' => $card['tone'] === 'ink',
                        ])>
                            <x-icon :name="$card['icon']" class="h-4 w-4" />
                        </span>
                    </div>
                    <p class="mt-2 flex items-baseline gap-1 truncate">
                        <span @class(['text-xs font-semibold', 'text-white/60' => $card['tone'] === 'dark', 'text-ink-400' => $card['tone'] !== 'dark'])>{{ $currency }}</span>
                        <span @class(['text-xl font-bold tracking-tight tabular-nums sm:text-2xl', 'text-accent-400' => $card['tone'] === 'dark', 'text-ink-900' => $card['tone'] !== 'dark'])>{{ number_format($card['value'], 2) }}</span>
                    </p>
                    <p @class(['mt-1 truncate text-[11px]', 'text-white/60' => $card['tone'] === 'dark', 'text-ink-500' => $card['tone'] !== 'dark'])>{{ $card['note'] }}</p>
                </div>
            @endforeach
        @endif
        <div class="panel p-4 sm:p-5">
            <div class="flex items-center justify-between gap-2">
                <p class="text-xs font-medium text-ink-500">Designs</p>
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-accent-500/15 text-accent-700 dark:text-accent-400">
                    <x-icon name="layout-dashboard" class="h-4 w-4" />
                </span>
            </div>
            <p class="mt-2 text-xl font-bold tracking-tight tabular-nums text-ink-900 sm:text-2xl">{{ number_format($summary['designs']) }}</p>
            <p class="mt-1 truncate text-[11px] text-ink-500">{{ number_format($summary['sold']) }} sold · {{ number_format($summary['inReview']) }} in review · {{ number_format($summary['approved']) }} approved</p>
        </div>
    </div>

    <div class="card mb-4 flex flex-wrap items-end gap-4 p-4">
        <div class="min-w-[220px] flex-1">
            <label class="form-label">Search</label>
            <input wire:model.live.debounce.400ms="search" type="text" placeholder="Design no, title…" class="field-input">
        </div>
        <div>
            <label class="form-label">Project</label>
            <select wire:model.live="project" class="field-input">
                <option value="">Any project</option>
                @foreach ($projects as $proj)
                    <option value="{{ $proj->id }}">{{ $proj->project_code }} — {{ $proj->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Type</label>
            <select wire:model.live="type" class="field-input">
                <option value="">All designs</option>
                <option value="project">Project designs</option>
                <option value="sold">Sold designs</option>
            </select>
        </div>
        <div>
            <label class="form-label">Category</label>
            <select wire:model.live="category" class="field-input">
                <option value="">Any category</option>
                @foreach ($categories as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Status</label>
            <select wire:model.live="status" class="field-input">
                <option value="">Any status</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button type="button" wire:click="resetFilters" class="btn-secondary">Reset</button>
    </div>

    @if ($designs->isEmpty())
        <div class="panel overflow-hidden">
            <x-empty-state
                title="No designs found"
                description="Upload a design for a project, or record a design sold on its own to a client."
            />
        </div>
    @else
        @php $canMoney = auth()->user()?->can('view_invoices'); @endphp
        <x-table>
            <thead>
                <tr>
                    <th>Design #</th>
                    <th>Title</th>
                    <th>Project / Client</th>
                    <th>Category</th>
                    <th class="!text-right">Design price</th>
                    <th>Revisions</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($designs as $design)
                    <tr wire:key="design-{{ $design->id }}">
                        <td class="font-mono">
                            <a href="{{ route('designs.show', $design) }}" wire:navigate class="text-accent-600 hover:underline">{{ $design->design_no }}</a>
                        </td>
                        <td class="max-w-[240px]">
                            <span class="block truncate font-medium text-ink-900">{{ $design->title }}</span>
                        </td>
                        <td class="max-w-[220px]">
                            @if ($design->project)
                                <a href="{{ route('projects.show', $design->project) }}" wire:navigate class="font-mono text-sm hover:underline">{{ $design->project->project_code }}</a>
                                <span class="block truncate text-xs text-ink-500">{{ $design->project->client?->name }}</span>
                            @else
                                <span class="chip !border-accent-500/40 !bg-accent-500/10 !text-accent-700 dark:!text-accent-400">Sold design</span>
                                <span class="mt-0.5 block truncate text-xs text-ink-500">{{ $design->client?->name ?? '—' }}</span>
                            @endif
                        </td>
                        <td>{{ $design->category?->label() ?? '—' }}</td>
                        <td class="whitespace-nowrap text-right tabular-nums">
                            @if ($design->price !== null)
                                <span class="font-medium text-ink-900">{{ number_format((float) $design->price, 2) }}</span>
                            @else
                                <span class="text-ink-400">—</span>
                            @endif
                            {{-- What's been billed/paid for it, when invoiced. --}}
                            @if ($canMoney && $design->billed_sum !== null)
                                @php $billed = (float) $design->billed_sum; $paid = (float) $design->paid_sum; @endphp
                                <span @class([
                                    'block text-[11px]',
                                    'text-success-700 dark:text-success-400' => $billed > 0 && $paid >= $billed,
                                    'text-ink-500' => ! ($billed > 0 && $paid >= $billed),
                                ])>
                                    {{ $billed > 0 && $paid >= $billed ? 'Paid in full' : 'Paid '.number_format($paid, 2).' of '.number_format($billed, 2) }}
                                </span>
                            @elseif ($canMoney && $design->isSold())
                                <span class="block text-[11px] text-ink-500">Not invoiced yet</span>
                            @endif
                        </td>
                        <td>{{ $design->revisions->count() }}</td>
                        <td>
                            <x-status-badge :label="$design->status->label()" :color="$design->status->color()" />
                        </td>
                        <td class="text-right">
                            <x-dropdown>
                                <a href="{{ route('designs.show', $design) }}" wire:navigate class="dropdown-item">View</a>
                                @can('update', $design)
                                    <a href="{{ route('designs.edit', $design) }}" wire:navigate class="dropdown-item">Edit</a>
                                @endcan
                                @if ($design->isSold())
                                    @can('create', \App\Models\Invoice::class)
                                        <a href="{{ route('invoices.create', ['billing' => 'design', 'design' => $design->id]) }}" wire:navigate class="dropdown-item">Create invoice</a>
                                    @endcan
                                @endif
                                @can('delete', $design)
                                    <x-confirm-dialog
                                        title="Delete this design?"
                                        :message='"This will remove {$design->design_no} and its revisions. This cannot be undone."'
                                        action="deleteDesign"
                                        :params="[$design->id]"
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
            {{ $designs->links() }}
        </div>
    @endif
</div>
