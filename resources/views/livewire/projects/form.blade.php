<div>
    <x-page-header :title='$project ? "Edit Project {$project->project_code}" : "New Project"'>
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Projects' => route('projects.index'), ($project ? 'Edit' : 'New') => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    @if ($lead_id)
        <p class="mb-4 rounded-lg bg-accent-50 px-4 py-2 text-sm text-accent-700">
            This project will be linked back to lead #{{ $lead_id }} once created.
        </p>
    @endif

    <form wire:submit="save" class="max-w-3xl space-y-6">
        <div class="panel grid grid-cols-2 gap-4 p-6">
            <div class="col-span-2">
                <x-input label="Name *" name="name" wire:model="name" />
            </div>
            <x-select label="Client *" name="client_id" wire:model="client_id">
                <option value="">Select…</option>
                @foreach ($clients as $client)
                    <option value="{{ $client->id }}">{{ $client->name }} ({{ $client->client_no }})</option>
                @endforeach
            </x-select>
            <x-select label="Type" name="type" wire:model="type">
                <option value="">Select…</option>
                @foreach ($types as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-select>
            <div class="col-span-2">
                <x-input label="Location" name="location" wire:model="location" />
            </div>
            <x-input label="Start date" name="start_date" type="date" wire:model="start_date" />
            <x-input label="Expected end date" name="expected_end_date" type="date" wire:model="expected_end_date" />
            <x-input label="Contract value" name="contract_value" type="number" step="0.01" min="0" wire:model="contract_value" />
            <x-input label="Estimated budget" name="estimated_budget" type="number" step="0.01" min="0" wire:model="estimated_budget" />
            <x-select label="Project manager" name="project_manager_id" wire:model="project_manager_id">
                <option value="">Unassigned</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </x-select>
            <x-select label="Designer" name="designer_id" wire:model="designer_id">
                <option value="">Unassigned</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </x-select>
            <x-select label="Site engineer" name="site_engineer_id" wire:model="site_engineer_id">
                <option value="">Unassigned</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </x-select>
            <x-select label="Supervisor" name="supervisor_id" wire:model="supervisor_id">
                <option value="">Unassigned</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
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
            <div class="col-span-2">
                <x-textarea label="Description" name="description" rows="3" wire:model="description" />
            </div>
        </div>

        <div class="flex items-center gap-3">
            <x-button type="submit">{{ $project ? 'Save changes' : 'Create project' }}</x-button>
            <x-button variant="secondary" href="{{ $project ? route('projects.show', $project) : route('projects.index') }}" wire:navigate>Cancel</x-button>
        </div>
    </form>
</div>
