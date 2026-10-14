<div>
    <x-page-header :title="$lead ? 'Edit Lead' : 'New Lead'" :description='$lead ? "Editing {$lead->lead_no}." : null'>
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Leads' => route('leads.index'), ($lead ? 'Edit' : 'New') => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    <form wire:submit="save" class="panel max-w-3xl space-y-6 p-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="col-span-2">
                <x-input label="Name *" name="name" wire:model="name" />
            </div>
            <x-input label="Phone" name="phone" wire:model="phone" />
            <x-input label="Email" name="email" type="email" wire:model="email" />
            <x-input label="Company name" name="company_name" wire:model="company_name" />
            <x-select label="Project type" name="project_type" wire:model="project_type">
                <option value="">Select…</option>
                @foreach ($projectTypes as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-select>
            <div class="col-span-2">
                <x-textarea label="Address" name="address" rows="2" wire:model="address" />
            </div>
            <x-input label="Estimated budget" name="estimated_budget" type="number" step="0.01" min="0" wire:model="estimated_budget" />
            <x-input label="Interested service" name="interested_service" wire:model="interested_service" />
            <x-select label="Lead source" name="lead_source_id" wire:model="lead_source_id">
                <option value="">Select…</option>
                @foreach ($sources as $source)
                    <option value="{{ $source->id }}">{{ $source->name }}</option>
                @endforeach
            </x-select>
            <x-select label="Assigned to" name="assigned_to" wire:model="assigned_to">
                <option value="">Unassigned</option>
                @foreach ($assignees as $assignee)
                    <option value="{{ $assignee->id }}">{{ $assignee->name }}</option>
                @endforeach
            </x-select>
            <x-select label="Priority *" name="priority" wire:model="priority">
                @foreach ($priorities as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-select>
            <x-select label="Status *" name="status" wire:model="status">
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-select>
            <x-input label="Expected closing date" name="expected_closing_date" type="date" wire:model="expected_closing_date" />
            <div class="col-span-2">
                <x-textarea label="Notes" name="notes" rows="3" wire:model="notes" />
            </div>
        </div>

        <div class="flex items-center gap-3 border-t border-ink-100 pt-5">
            <x-button type="submit">{{ $lead ? 'Save changes' : 'Create lead' }}</x-button>
            <x-button variant="secondary" href="{{ route('leads.index') }}" wire:navigate>Cancel</x-button>
        </div>
    </form>
</div>
