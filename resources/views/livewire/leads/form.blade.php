<div>
    <x-breadcrumbs :items="['Leads' => route('leads.index'), ($lead ? 'Edit' : 'New') => null]" class="mb-4" />

    <h1 class="mb-6 text-2xl font-semibold text-gray-900">
        {{ $lead ? "Edit Lead {$lead->lead_no}" : 'New Lead' }}
    </h1>

    <form wire:submit="save" class="card max-w-3xl space-y-6 p-6">
        <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
                <label class="form-label">Name *</label>
                <input wire:model="name" type="text" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('name') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Phone</label>
                <input wire:model="phone" type="text" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('phone') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Email</label>
                <input wire:model="email" type="email" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('email') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Company name</label>
                <input wire:model="company_name" type="text" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
            </div>
            <div>
                <label class="form-label">Project type</label>
                <select wire:model="project_type" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                    <option value="">Select…</option>
                    @foreach ($projectTypes as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-2">
                <label class="form-label">Address</label>
                <textarea wire:model="address" rows="2" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"></textarea>
            </div>
            <div>
                <label class="form-label">Estimated budget</label>
                <input wire:model="estimated_budget" type="number" step="0.01" min="0" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('estimated_budget') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Interested service</label>
                <input wire:model="interested_service" type="text" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
            </div>
            <div>
                <label class="form-label">Lead source</label>
                <select wire:model="lead_source_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                    <option value="">Select…</option>
                    @foreach ($sources as $source)
                        <option value="{{ $source->id }}">{{ $source->name }}</option>
                    @endforeach
                </select>
                @error('lead_source_id') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Assigned to</label>
                <select wire:model="assigned_to" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                    <option value="">Unassigned</option>
                    @foreach ($assignees as $assignee)
                        <option value="{{ $assignee->id }}">{{ $assignee->name }}</option>
                    @endforeach
                </select>
                @error('assigned_to') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Priority *</label>
                <select wire:model="priority" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                    @foreach ($priorities as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Status *</label>
                <select wire:model="status" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Expected closing date</label>
                <input wire:model="expected_closing_date" type="date" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('expected_closing_date') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div class="col-span-2">
                <label class="form-label">Notes</label>
                <textarea wire:model="notes" rows="3" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"></textarea>
                @error('notes') <p class="form-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex items-center gap-3 border-t border-gray-100 pt-5">
            <button type="submit" class="btn-primary">{{ $lead ? 'Save changes' : 'Create lead' }}</button>
            <a href="{{ route('leads.index') }}" wire:navigate class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
