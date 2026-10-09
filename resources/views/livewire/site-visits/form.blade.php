<div>
    <x-breadcrumbs :items="['Site Visits' => route('site-visits.index'), ($siteVisit ? 'Edit' : 'New') => null]" class="mb-4" />

    <h1 class="mb-6 text-2xl font-semibold text-gray-900">
        {{ $siteVisit ? "Edit Site Visit {$siteVisit->sv_no}" : 'New Site Visit' }}
    </h1>

    <form wire:submit="save" class="max-w-4xl space-y-6">
        <div class="card grid grid-cols-2 gap-4 p-6">
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
                <label class="form-label">Project (optional)</label>
                <select wire:model="project_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                    <option value="">No project yet</option>
                    @foreach ($projects as $proj)
                        <option value="{{ $proj->id }}">{{ $proj->project_code }} — {{ $proj->name }}</option>
                    @endforeach
                </select>
                @error('project_id') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Visit date *</label>
                <input wire:model="visit_date" type="date" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('visit_date') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div class="col-span-2">
                <label class="form-label">Site address</label>
                <textarea wire:model="site_address" rows="2" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"></textarea>
            </div>
            <div>
                <label class="form-label">Purpose</label>
                <input wire:model="purpose" type="text" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
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
            <div class="col-span-2">
                <label class="form-label">Requirements</label>
                <textarea wire:model="requirements" rows="3" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"></textarea>
            </div>
            <div class="col-span-2">
                <label class="form-label">Notes</label>
                <textarea wire:model="notes" rows="2" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"></textarea>
            </div>
        </div>

        <div class="card p-6">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Rooms / Areas</h2>
                <button type="button" wire:click="addRoom" class="text-sm text-brand-600 hover:underline">+ Add room</button>
            </div>

            <div class="space-y-3">
                @foreach ($rooms as $index => $room)
                    <div wire:key="room-{{ $index }}" class="grid grid-cols-12 gap-2 rounded-lg border border-gray-100 p-3">
                        <div class="col-span-3">
                            <input wire:model="rooms.{{ $index }}.area_name" type="text" placeholder="Room / area name *" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                            @error("rooms.{$index}.area_name") <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="col-span-2">
                            <input wire:model="rooms.{{ $index }}.length" type="number" step="0.01" min="0" placeholder="Length" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                        </div>
                        <div class="col-span-2">
                            <input wire:model="rooms.{{ $index }}.width" type="number" step="0.01" min="0" placeholder="Width" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                        </div>
                        <div class="col-span-2">
                            <input wire:model="rooms.{{ $index }}.height" type="number" step="0.01" min="0" placeholder="Height" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                        </div>
                        <div class="col-span-2">
                            <input wire:model="rooms.{{ $index }}.notes" type="text" placeholder="Notes" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                        </div>
                        <div class="col-span-1 flex items-center justify-end">
                            <button type="button" wire:click="removeRoom({{ $index }})" class="text-sm text-red-500 hover:text-red-700">✕</button>
                        </div>
                    </div>
                @endforeach
            </div>
            <p class="mt-3 text-xs text-gray-400">Area is calculated automatically from length × width.</p>
        </div>

        <div class="card p-6">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Photos</h2>
            <input wire:model="photos" type="file" multiple accept="image/*" class="block w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-brand-700 hover:file:bg-brand-100">
            @error('photos.*') <p class="form-error">{{ $message }}</p> @enderror
            <div wire:loading wire:target="photos" class="mt-2 text-xs text-gray-400">Uploading…</div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary">{{ $siteVisit ? 'Save changes' : 'Create site visit' }}</button>
            <a href="{{ route('site-visits.index') }}" wire:navigate class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
