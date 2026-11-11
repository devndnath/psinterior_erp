<div>
    <x-breadcrumbs :items="['Designs' => route('designs.index'), $design->design_no => null]" class="mb-4" />

    <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold text-gray-900">{{ $design->design_no }}</h1>
                <x-status-badge :label="$design->status->label()" :color="$design->status->color()" />
            </div>
            <p class="mt-1 text-sm text-gray-500">
                {{ $design->title }}
                @if ($design->project)
                    — <a href="{{ route('projects.show', $design->project) }}" wire:navigate class="text-brand-600 hover:underline">{{ $design->project->project_code }}</a>
                    @if ($design->project->client)
                        — {{ $design->project->client->name }}
                    @endif
                @else
                    — Sold design{{ $design->client ? ' — '.$design->client->name : '' }}
                @endif
            </p>
        </div>
        <div class="flex items-center gap-3">
            @can('update', $design)
                <a href="{{ route('designs.edit', $design) }}" wire:navigate class="btn-secondary">Edit</a>
            @endcan
            @can('delete', $design)
                <x-confirm-dialog
                    title="Delete this design?"
                    :message='"This will remove {$design->design_no} and its revisions. This cannot be undone."'
                    action="deleteDesign"
                >
                    <x-slot:trigger>
                        <button type="button" class="btn-secondary text-red-600">Delete</button>
                    </x-slot:trigger>
                </x-confirm-dialog>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Details</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-500">Category</dt><dd class="font-medium text-gray-900">{{ $design->category?->label() ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Uploaded by</dt><dd class="font-medium text-gray-900">{{ $design->uploader?->name ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Client decision</dt>
                        <dd class="font-medium text-gray-900">
                            @if ($design->client_approval_status)
                                <x-status-badge :label="$design->client_approval_status->label()" :color="$design->client_approval_status->color()" />
                            @else
                                Pending
                            @endif
                        </dd>
                    </div>
                    <div class="col-span-2"><dt class="text-gray-500">Description</dt><dd class="whitespace-pre-line font-medium text-gray-900">{{ $design->description ?: '—' }}</dd></div>
                </dl>
            </div>

            <div class="card p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Revision history</h2>
                    @can('uploadRevision', $design)
                        <button type="button" wire:click="toggleRevisionForm" class="text-sm text-brand-600 hover:underline">
                            {{ $showRevisionForm ? 'Cancel' : '+ Add revision' }}
                        </button>
                    @endcan
                </div>

                @if ($showRevisionForm)
                    <form wire:submit="addRevision" class="mb-6 space-y-3 rounded-lg border border-gray-100 p-4">
                        <div>
                            <label class="form-label">File *</label>
                            <input wire:model="revisionFile" type="file" class="block w-full text-sm text-gray-600 file:mr-4 file:rounded-full file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-brand-700 hover:file:bg-brand-100">
                            @error('revisionFile') <p class="form-error">{{ $message }}</p> @enderror
                            <div wire:loading wire:target="revisionFile" class="mt-2 text-xs text-gray-400">Uploading…</div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="form-label">Drawing No.</label>
                                <input type="text" wire:model="revisionDrawingNo" placeholder="e.g. DRG-ELV-03" class="field-input">
                                @error('revisionDrawingNo') <p class="form-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="form-label">Issue Date</label>
                                <input type="date" wire:model="revisionIssueDate" class="field-input">
                                @error('revisionIssueDate') <p class="form-error">{{ $message }}</p> @enderror
                                <p class="mt-1 text-xs text-gray-400">Defaults to today if left blank.</p>
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Notes</label>
                            <textarea wire:model="revisionDescription" rows="2" class="field-input"></textarea>
                            @error('revisionDescription') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <button type="submit" class="btn-primary">Upload revision</button>
                    </form>
                @endif

                @if ($design->revisions->isEmpty())
                    <p class="text-sm text-gray-500">No revisions uploaded yet.</p>
                @else
                    <div class="space-y-4">
                        @foreach ($design->revisions as $revision)
                            @php($isSuperseded = $revision->version < $latestRevisionVersion)
                            <div wire:key="revision-{{ $revision->id }}" class="rounded-lg border border-gray-100 p-4 {{ $isSuperseded ? 'opacity-70' : '' }}">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">
                                            Version {{ $revision->version }}
                                            @if ($revision->drawing_no)
                                                <span class="text-gray-500">· {{ $revision->drawing_no }}</span>
                                            @endif
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ $revision->uploader?->name ?? '—' }} · {{ $revision->created_at?->format('d M, Y H:i') }}
                                            @if ($revision->issue_date)
                                                · Issued {{ $revision->issue_date->format('d M, Y') }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @if ($isSuperseded)
                                            <x-status-badge label="Superseded — do not use on site" color="red" />
                                        @endif
                                        <x-status-badge :label="$revision->status->label()" :color="$revision->status->color()" />
                                    </div>
                                </div>

                                @if ($revision->description)
                                    <p class="mt-2 whitespace-pre-line text-sm text-gray-600">{{ $revision->description }}</p>
                                @endif

                                @if ($revision->file)
                                    <a href="{{ $revision->file->url() }}" target="_blank" class="mt-2 inline-flex items-center text-sm text-brand-600 hover:underline">
                                        {{ $revision->file->file_name }} ({{ $revision->file->humanFileSize() }})
                                    </a>
                                @endif

                                @if ($revision->client_feedback)
                                    <p class="mt-2 rounded-lg bg-gray-50 p-2 text-sm text-gray-600">
                                        <span class="font-medium text-gray-700">Client feedback:</span> {{ $revision->client_feedback }}
                                    </p>
                                @endif

                                @if ($revision->approvals->isNotEmpty())
                                    <div class="mt-3 space-y-1 border-t border-gray-100 pt-3">
                                        @foreach ($revision->approvals as $approval)
                                            <p class="text-xs text-gray-500">
                                                <span class="font-medium text-gray-700">{{ $approval->decision->label() }}</span>
                                                by {{ $approval->client?->name ?? 'Client' }} on {{ $approval->decided_at?->format('d M, Y H:i') }}
                                            </p>
                                        @endforeach
                                    </div>
                                @endif

                                @can('recordApproval', $design)
                                    <div class="mt-3 border-t border-gray-100 pt-3">
                                        @if ($decisionRevisionId === $revision->id)
                                            <form wire:submit="recordDecision" class="space-y-3">
                                                <div>
                                                    <label class="form-label">Client decision *</label>
                                                    <select wire:model="decision" class="field-input">
                                                        <option value="">Select…</option>
                                                        @foreach ($decisionOptions as $value => $label)
                                                            <option value="{{ $value }}">{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('decision') <p class="form-error">{{ $message }}</p> @enderror
                                                </div>
                                                <div>
                                                    <label class="form-label">Comment</label>
                                                    <textarea wire:model="decisionComment" rows="2" class="field-input"></textarea>
                                                    @error('decisionComment') <p class="form-error">{{ $message }}</p> @enderror
                                                </div>
                                                <div class="flex items-center gap-3">
                                                    <button type="submit" class="btn-primary">Save decision</button>
                                                    <button type="button" wire:click="cancelDecisionForm" class="btn-secondary">Cancel</button>
                                                </div>
                                            </form>
                                        @else
                                            <button type="button" wire:click="openDecisionForm({{ $revision->id }})" class="text-sm text-brand-600 hover:underline">
                                                Record client decision
                                            </button>
                                        @endif
                                    </div>
                                @endcan
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div>
            @if ($design->isSold())
                {{-- A design sold on its own: client, price and what's
                     been invoiced / paid for it. --}}
                <div class="card p-6">
                    <div class="mb-4 flex items-center justify-between gap-2">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Sale</h2>
                        <span class="chip !border-accent-500/40 !bg-accent-500/10 !text-accent-700 dark:!text-accent-400">Sold design</span>
                    </div>
                    <dl class="space-y-2 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-ink-500">Client</dt>
                            <dd class="truncate font-medium text-ink-900">
                                @if ($design->client)
                                    <a href="{{ route('clients.show', $design->client) }}" wire:navigate class="hover:underline">{{ $design->client->name }}</a>
                                @else
                                    —
                                @endif
                            </dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-ink-500">Design price</dt>
                            <dd class="font-semibold tabular-nums text-ink-900">{{ $design->price !== null ? number_format((float) $design->price, 2) : '—' }}</dd>
                        </div>
                    </dl>
                    @can('view_invoices')
                        @php($active = $design->invoices->where('status', '!=', \App\Enums\InvoiceStatus::Cancelled))
                        <div class="mt-4 border-t border-ink-100 pt-4">
                            @forelse ($active as $inv)
                                <div class="flex items-center justify-between gap-2 py-1 text-sm">
                                    <a href="{{ route('invoices.show', $inv) }}" wire:navigate class="font-medium text-accent-600 hover:underline">{{ $inv->invoice_no }}</a>
                                    <span class="tabular-nums text-ink-600">{{ number_format((float) $inv->paid_amount, 2) }} / {{ number_format((float) $inv->grand_total, 2) }}</span>
                                </div>
                            @empty
                                <p class="text-sm text-ink-500">Not invoiced yet.</p>
                            @endforelse
                        </div>
                    @endcan
                    @can('create', \App\Models\Invoice::class)
                        <a href="{{ route('invoices.create', ['billing' => 'design', 'design' => $design->id]) }}" wire:navigate class="btn-primary mt-4 w-full justify-center">Create invoice</a>
                    @endcan
                </div>
            @else
                <div class="card p-6">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Project</h2>
                    <p class="text-sm font-medium text-gray-900">
                        <a href="{{ route('projects.show', $design->project) }}" wire:navigate class="text-brand-600 hover:underline">{{ $design->project->project_code }}</a>
                    </p>
                    <p class="mt-1 text-sm text-gray-500">{{ $design->project->name }}</p>
                    @if ($design->price !== null)
                        <p class="mt-3 flex items-center justify-between text-sm"><span class="text-ink-500">Design price</span><span class="font-semibold tabular-nums text-ink-900">{{ number_format((float) $design->price, 2) }}</span></p>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
