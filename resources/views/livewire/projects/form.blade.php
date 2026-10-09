<div>
    <x-breadcrumbs :items="['Projects' => route('projects.index'), ($project ? 'Edit' : 'New') => null]" class="mb-4" />

    <h1 class="mb-6 text-2xl font-semibold text-gray-900">
        {{ $project ? "Edit Project {$project->project_code}" : 'New Project' }}
    </h1>

    @if ($lead_id)
        <p class="mb-4 rounded-lg bg-brand-50 px-4 py-2 text-sm text-brand-700">
            This project will be linked back to lead #{{ $lead_id }} once created.
        </p>
    @endif

    <form wire:submit="save" class="card max-w-3xl space-y-6 p-6">
        <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
                <label class="form-label">Name *</label>
                <input wire:model="name" type="text" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('name') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Client *</label>
                <select wire:model="client_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                    <option value="">Select…</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->name }} ({{ $client->client_no }})</option>
                    @endforeach
                </select>
                @error('client_id') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Type</label>
                <select wire:model="type" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                    <option value="">Select…</option>
                    @foreach ($types as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-2">
                <label class="form-label">Location</label>
                <input wire:model="location" type="text" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
            </div>
            <div>
                <label class="form-label">Start date</label>
                <input wire:model="start_date" type="date" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('start_date') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Expected end date</label>
                <input wire:model="expected_end_date" type="date" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('expected_end_date') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Contract value</label>
                <input wire:model="contract_value" type="number" step="0.01" min="0" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('contract_value') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Estimated budget</label>
                <input wire:model="estimated_budget" type="number" step="0.01" min="0" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('estimated_budget') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Project manager</label>
                <select wire:model="project_manager_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                    <option value="">Unassigned</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Designer</label>
                <select wire:model="designer_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                    <option value="">Unassigned</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Site engineer</label>
                <select wire:model="site_engineer_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                    <option value="">Unassigned</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Supervisor</label>
                <select wire:model="supervisor_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                    <option value="">Unassigned</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
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
            <div class="col-span-2">
                <label class="form-label">Description</label>
                <textarea wire:model="description" rows="3" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"></textarea>
                @error('description') <p class="form-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex items-center gap-3 border-t border-gray-100 pt-5">
            <button type="submit" class="btn-primary">{{ $project ? 'Save changes' : 'Create project' }}</button>
            <a href="{{ $project ? route('projects.show', $project) : route('projects.index') }}" wire:navigate class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
