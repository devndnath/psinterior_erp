<div>
    <x-breadcrumbs :items="['Clients' => route('clients.index'), $client->client_no => null]" class="mb-4" />

    <div class="mb-6 flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">{{ $client->name }}</h1>
            <p class="mt-1 font-mono text-sm text-gray-500">{{ $client->client_no }}</p>
        </div>
        <div class="flex items-center gap-3">
            <x-status-badge :label="$client->type->label()" color="indigo" />
            @can('update', $client)
                <a href="{{ route('clients.edit', $client) }}" wire:navigate class="btn-secondary">Edit</a>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Client details</h2>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-500">Phone</dt><dd class="font-medium text-gray-900">{{ $client->phone ?: '—' }}</dd></div>
                    <div><dt class="text-gray-500">Email</dt><dd class="font-medium text-gray-900">{{ $client->email ?: '—' }}</dd></div>
                    <div><dt class="text-gray-500">Company</dt><dd class="font-medium text-gray-900">{{ $client->company_name ?: '—' }}</dd></div>
                    <div><dt class="text-gray-500">Tax ID</dt><dd class="font-medium text-gray-900">{{ $client->tax_id ?: '—' }}</dd></div>
                    <div><dt class="text-gray-500">Assigned to</dt><dd class="font-medium text-gray-900">{{ $client->assignee?->name ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Portal login</dt><dd class="font-medium text-gray-900">{{ $client->portalUser?->email ?? 'Not set up' }}</dd></div>
                    <div class="col-span-2"><dt class="text-gray-500">Address</dt><dd class="font-medium text-gray-900">{{ $client->address ?: '—' }}</dd></div>
                    <div><dt class="text-gray-500">Billing address</dt><dd class="font-medium text-gray-900">{{ $client->billing_address ?: '—' }}</dd></div>
                    <div><dt class="text-gray-500">Site address</dt><dd class="font-medium text-gray-900">{{ $client->site_address ?: '—' }}</dd></div>
                    <div class="col-span-2"><dt class="text-gray-500">Notes</dt><dd class="whitespace-pre-line font-medium text-gray-900">{{ $client->notes ?: '—' }}</dd></div>
                </dl>
            </div>

            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Leads</h2>
                @if ($leads->isEmpty())
                    <p class="text-sm text-gray-500">No leads linked to this client.</p>
                @else
                    <ul class="divide-y divide-gray-100">
                        @foreach ($leads as $lead)
                            <li class="flex items-center justify-between py-2 text-sm">
                                <a href="{{ route('leads.show', $lead) }}" wire:navigate class="font-medium text-brand-600 hover:underline">{{ $lead->lead_no }} — {{ $lead->name }}</a>
                                <x-status-badge :label="$lead->status->label()" :color="$lead->status->color()" />
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Site visits</h2>
                @if ($siteVisits->isEmpty())
                    <p class="text-sm text-gray-500">No site visits logged yet.</p>
                @else
                    <ul class="divide-y divide-gray-100">
                        @foreach ($siteVisits as $visit)
                            <li class="flex items-center justify-between py-2 text-sm">
                                <a href="{{ route('site-visits.show', $visit) }}" wire:navigate class="font-medium text-brand-600 hover:underline">{{ $visit->sv_no }}</a>
                                <span class="text-gray-500">{{ $visit->visit_date?->format('d M, Y') }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="card p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Projects</h2>
                    @can('create', \App\Models\Project::class)
                        <a href="{{ route('projects.create', ['client' => $client->id]) }}" wire:navigate class="text-sm text-brand-600 hover:underline">+ New Project</a>
                    @endcan
                </div>
                @if ($projects->isEmpty())
                    <p class="text-sm text-gray-500">No projects opened for this client yet.</p>
                @else
                    <ul class="divide-y divide-gray-100">
                        @foreach ($projects as $project)
                            <li class="flex items-center justify-between py-2 text-sm">
                                <a href="{{ route('projects.show', $project) }}" wire:navigate class="font-medium text-brand-600 hover:underline">{{ $project->project_code }} — {{ $project->name }}</a>
                                <x-status-badge :label="$project->status->label()" :color="$project->status->color()" />
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="card p-6 text-sm text-gray-400">
                Quotations, contracts, invoices, payments and meetings will appear here once those modules ship (Phases 4+).
            </div>
        </div>

        <div>
            <div class="card p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Contacts</h2>
                    @can('update', $client)
                        <button type="button" wire:click="$toggle('showContactForm')" class="text-sm text-brand-600 hover:underline">
                            {{ $showContactForm ? 'Cancel' : '+ Add' }}
                        </button>
                    @endcan
                </div>

                @if ($showContactForm)
                    <form wire:submit="addContact" class="mb-5 space-y-3 border-b border-gray-100 pb-5">
                        <div>
                            <input wire:model="contact_name" type="text" placeholder="Name *" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                            @error('contact_name') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <input wire:model="contact_designation" type="text" placeholder="Designation" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                        <input wire:model="contact_phone" type="text" placeholder="Phone" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                        <div>
                            <input wire:model="contact_email" type="email" placeholder="Email" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                            @error('contact_email') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <label class="flex items-center gap-2 text-sm text-gray-600">
                            <input wire:model="contact_is_primary" type="checkbox" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                            Primary contact
                        </label>
                        <button type="submit" class="btn-primary w-full">Add contact</button>
                    </form>
                @endif

                @if ($contacts->isEmpty())
                    <p class="text-sm text-gray-500">No contacts added yet.</p>
                @else
                    <ul class="space-y-3">
                        @foreach ($contacts as $contact)
                            <li wire:key="contact-{{ $contact->id }}" class="rounded-lg border border-gray-100 p-3">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $contact->name }}
                                        @if ($contact->is_primary)
                                            <x-status-badge label="Primary" color="green" class="ml-1" />
                                        @endif
                                    </p>
                                    @can('update', $client)
                                        <button type="button" wire:click="removeContact({{ $contact->id }})" wire:confirm="Remove this contact?" class="text-xs text-red-500 hover:text-red-700">Remove</button>
                                    @endcan
                                </div>
                                @if ($contact->designation)
                                    <p class="text-xs text-gray-500">{{ $contact->designation }}</p>
                                @endif
                                @if ($contact->phone)
                                    <p class="text-xs text-gray-500">{{ $contact->phone }}</p>
                                @endif
                                @if ($contact->email)
                                    <p class="text-xs text-gray-500">{{ $contact->email }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>
