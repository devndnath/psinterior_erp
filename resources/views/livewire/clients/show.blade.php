<div>
    <x-page-header :title="$client->name">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Clients' => route('clients.index'), $client->client_no => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            <x-status-badge :label="$client->type->label()" color="indigo" />
            @can('update', $client)
                <x-button variant="secondary" href="{{ route('clients.edit', $client) }}" wire:navigate>Edit</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <p class="-mt-4 mb-6 font-mono text-sm text-ink-500">{{ $client->client_no }}</p>

    @if ($generatedPassword)
        <div x-data="{ open: true }" x-show="open" class="panel mb-6 border border-success-200 bg-success-50 p-4">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold text-success-800">{{ $passwordJustReset ? 'Portal password reset for' : 'Portal login created for' }} {{ $client->portalUser?->email }}</p>
                    <p class="mt-1 text-sm text-success-700">
                        {{ $passwordJustReset ? 'New password' : 'Temporary password' }}: <code class="rounded bg-white px-2 py-0.5 font-mono">{{ $generatedPassword }}</code>
                        — this app doesn't send login emails, so copy it now and share it with {{ $client->name }}. It won't be shown again.
                    </p>
                </div>
                <button type="button" x-on:click="open = false" class="shrink-0 text-success-600 hover:text-success-800">&times;</button>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Client details</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-ink-500">Phone</dt><dd class="font-medium text-ink-900">{{ $client->phone ?: '—' }}</dd></div>
                    <div><dt class="text-ink-500">Email</dt><dd class="font-medium text-ink-900">{{ $client->email ?: '—' }}</dd></div>
                    <div><dt class="text-ink-500">Company</dt><dd class="font-medium text-ink-900">{{ $client->company_name ?: '—' }}</dd></div>
                    <div><dt class="text-ink-500">Tax ID</dt><dd class="font-medium text-ink-900">{{ $client->tax_id ?: '—' }}</dd></div>
                    <div><dt class="text-ink-500">Assigned to</dt><dd class="font-medium text-ink-900">{{ $client->assignee?->name ?? '—' }}</dd></div>
                    <div>
                        <dt class="text-ink-500">Portal login</dt>
                        <dd class="font-medium text-ink-900">
                            @if ($client->portalUser)
                                <div class="flex flex-wrap items-center gap-2">
                                    <span>{{ $client->portalUser->email }}</span>
                                    <x-status-badge :label="$client->portalUser->is_active ? 'Active' : 'Inactive'" :color="$client->portalUser->is_active ? 'green' : 'gray'" />
                                    @can('update', $client)
                                        <button type="button" wire:click="resetPortalPassword" wire:confirm="Reset this client's portal password? The old password stops working immediately." class="text-xs font-medium text-accent-600 hover:underline">Reset password</button>
                                        <span class="text-ink-300">·</span>
                                        <button
                                            type="button"
                                            wire:click="togglePortalActive"
                                            @if ($client->portalUser->is_active)
                                                wire:confirm="Deactivate this client's portal access? They won't be able to log in until it's reactivated."
                                            @endif
                                            class="text-xs font-medium {{ $client->portalUser->is_active ? 'text-danger-600' : 'text-success-600' }} hover:underline"
                                        >
                                            {{ $client->portalUser->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    @endcan
                                </div>
                            @else
                                Not set up
                                @can('update', $client)
                                    <a href="{{ route('clients.edit', $client) }}" wire:navigate class="ml-1 text-xs text-accent-600 hover:underline">Create one →</a>
                                @endcan
                            @endif
                        </dd>
                    </div>
                    <div class="col-span-2"><dt class="text-ink-500">Address</dt><dd class="font-medium text-ink-900">{{ $client->address ?: '—' }}</dd></div>
                    <div><dt class="text-ink-500">Billing address</dt><dd class="font-medium text-ink-900">{{ $client->billing_address ?: '—' }}</dd></div>
                    <div><dt class="text-ink-500">Site address</dt><dd class="font-medium text-ink-900">{{ $client->site_address ?: '—' }}</dd></div>
                    <div class="col-span-2"><dt class="text-ink-500">Notes</dt><dd class="whitespace-pre-line font-medium text-ink-900">{{ $client->notes ?: '—' }}</dd></div>
                </dl>
            </div>

            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Leads</h2>
                @if ($leads->isEmpty())
                    <p class="text-sm text-ink-500">No leads linked to this client.</p>
                @else
                    <ul class="divide-y divide-ink-100">
                        @foreach ($leads as $lead)
                            <li class="flex items-center justify-between py-2 text-sm">
                                <a href="{{ route('leads.show', $lead) }}" wire:navigate class="font-medium text-accent-600 hover:underline">{{ $lead->lead_no }} — {{ $lead->name }}</a>
                                <x-status-badge :label="$lead->status->label()" :color="$lead->status->color()" />
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Site visits</h2>
                @if ($siteVisits->isEmpty())
                    <p class="text-sm text-ink-500">No site visits logged yet.</p>
                @else
                    <ul class="divide-y divide-ink-100">
                        @foreach ($siteVisits as $visit)
                            <li class="flex items-center justify-between py-2 text-sm">
                                <a href="{{ route('site-visits.show', $visit) }}" wire:navigate class="font-medium text-accent-600 hover:underline">{{ $visit->sv_no }}</a>
                                <span class="text-ink-500">{{ $visit->visit_date?->format('d M, Y') }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="panel p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-ink-500">Projects</h2>
                    @can('create', \App\Models\Project::class)
                        <a href="{{ route('projects.create', ['client' => $client->id]) }}" wire:navigate class="text-sm text-accent-600 hover:underline">+ New Project</a>
                    @endcan
                </div>
                @if ($projects->isEmpty())
                    <p class="text-sm text-ink-500">No projects opened for this client yet.</p>
                @else
                    <ul class="divide-y divide-ink-100">
                        @foreach ($projects as $project)
                            <li class="flex items-center justify-between py-2 text-sm">
                                <a href="{{ route('projects.show', $project) }}" wire:navigate class="font-medium text-accent-600 hover:underline">{{ $project->project_code }} — {{ $project->name }}</a>
                                <x-status-badge :label="$project->status->label()" :color="$project->status->color()" />
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            @can('view_quotations')
                <div class="panel p-6">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Quotations</h2>
                    @if ($quotations->isEmpty())
                        <p class="text-sm text-ink-500">No quotations sent to this client yet.</p>
                    @else
                        <ul class="divide-y divide-ink-100">
                            @foreach ($quotations as $quotation)
                                <li class="flex items-center justify-between py-2 text-sm">
                                    <a href="{{ route('quotations.show', $quotation) }}" wire:navigate class="font-medium text-accent-600 hover:underline">{{ $quotation->quotation_no }}</a>
                                    <x-status-badge :label="$quotation->status->label()" :color="$quotation->status->color()" />
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endcan

            @can('view_contracts')
                <div class="panel p-6">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Contracts</h2>
                    @if ($contracts->isEmpty())
                        <p class="text-sm text-ink-500">No contracts with this client yet.</p>
                    @else
                        <ul class="divide-y divide-ink-100">
                            @foreach ($contracts as $contract)
                                <li class="flex items-center justify-between py-2 text-sm">
                                    <a href="{{ route('contracts.show', $contract) }}" wire:navigate class="font-medium text-accent-600 hover:underline">{{ $contract->contract_no }}</a>
                                    <x-status-badge :label="$contract->status->label()" :color="$contract->status->color()" />
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endcan

            @can('view_invoices')
                <div class="panel p-6">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Invoices</h2>
                    @if ($invoices->isEmpty())
                        <p class="text-sm text-ink-500">No invoices raised for this client yet.</p>
                    @else
                        <ul class="divide-y divide-ink-100">
                            @foreach ($invoices as $invoice)
                                <li class="flex items-center justify-between py-2 text-sm">
                                    <a href="{{ route('invoices.show', $invoice) }}" wire:navigate class="font-medium text-accent-600 hover:underline">{{ $invoice->invoice_no }}</a>
                                    <x-status-badge :label="$invoice->status->label()" :color="$invoice->status->color()" />
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endcan

            @can('view_payments')
                <div class="panel p-6">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Payments</h2>
                    @if ($payments->isEmpty())
                        <p class="text-sm text-ink-500">No payments received from this client yet.</p>
                    @else
                        <ul class="divide-y divide-ink-100">
                            @foreach ($payments as $payment)
                                <li class="flex items-center justify-between py-2 text-sm">
                                    <a href="{{ route('payments.show', $payment) }}" wire:navigate class="font-medium text-accent-600 hover:underline">{{ $payment->payment_no }}</a>
                                    <span class="text-ink-500">{{ number_format($payment->amount, 2) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endcan

            <div class="panel p-6 text-sm text-ink-400">
                Meetings will appear here once that module ships (Phase 7+).
            </div>
        </div>

        <div>
            <div class="panel p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-ink-500">Contacts</h2>
                    @can('update', $client)
                        <button type="button" wire:click="$toggle('showContactForm')" class="text-sm text-accent-600 hover:underline">
                            {{ $showContactForm ? 'Cancel' : '+ Add' }}
                        </button>
                    @endcan
                </div>

                {{-- A single-entry "add one contact" form, not a repeating
                     fieldset — the new <x-input>/<x-select> label+error
                     contract fits it cleanly, unlike the contact *list*
                     below which renders an arbitrary number of existing
                     ClientContact rows and stays hand-rolled. --}}
                @if ($showContactForm)
                    <form wire:submit="addContact" class="mb-5 space-y-3 border-b border-ink-100 pb-5">
                        <x-input name="contact_name" placeholder="Name *" wire:model="contact_name" />
                        <x-input name="contact_designation" placeholder="Designation" wire:model="contact_designation" />
                        <x-input name="contact_phone" placeholder="Phone" wire:model="contact_phone" />
                        <x-input name="contact_email" type="email" placeholder="Email" wire:model="contact_email" />
                        <label class="flex items-center gap-2 text-sm text-ink-600">
                            <input wire:model="contact_is_primary" type="checkbox" class="rounded border-ink-300 text-accent-600 focus:ring-accent-500">
                            Primary contact
                        </label>
                        <x-button type="submit" class="w-full">Add contact</x-button>
                    </form>
                @endif

                @if ($contacts->isEmpty())
                    <p class="text-sm text-ink-500">No contacts added yet.</p>
                @else
                    <ul class="space-y-3">
                        @foreach ($contacts as $contact)
                            <li wire:key="contact-{{ $contact->id }}" class="rounded-lg border border-ink-100 p-3">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-ink-900">
                                        {{ $contact->name }}
                                        @if ($contact->is_primary)
                                            <x-status-badge label="Primary" color="green" class="ml-1" />
                                        @endif
                                    </p>
                                    @can('update', $client)
                                        <button type="button" wire:click="removeContact({{ $contact->id }})" wire:confirm="Remove this contact?" class="text-xs text-danger-600 hover:text-danger-700">Remove</button>
                                    @endcan
                                </div>
                                @if ($contact->designation)
                                    <p class="text-xs text-ink-500">{{ $contact->designation }}</p>
                                @endif
                                @if ($contact->phone)
                                    <p class="text-xs text-ink-500">{{ $contact->phone }}</p>
                                @endif
                                @if ($contact->email)
                                    <p class="text-xs text-ink-500">{{ $contact->email }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>
