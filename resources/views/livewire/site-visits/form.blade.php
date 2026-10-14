<div>
    <x-page-header :title="$siteVisit ? 'Edit Site Visit' : 'New Site Visit'" :description='$siteVisit ? "Editing {$siteVisit->sv_no}." : null'>
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Site Visits' => route('site-visits.index'), ($siteVisit ? 'Edit' : 'New') => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    <form wire:submit="save" class="max-w-4xl space-y-6">
        <div class="panel grid grid-cols-1 sm:grid-cols-2 gap-4 p-6">
            <x-select label="Client *" name="client_id" wire:model="client_id">
                <option value="">Select…</option>
                @foreach ($clients as $client)
                    <option value="{{ $client->id }}">{{ $client->name }} ({{ $client->client_no }})</option>
                @endforeach
            </x-select>
            <x-select label="Project (optional)" name="project_id" wire:model="project_id">
                <option value="">No project yet</option>
                @foreach ($projects as $proj)
                    <option value="{{ $proj->id }}">{{ $proj->project_code }} — {{ $proj->name }}</option>
                @endforeach
            </x-select>
            <x-input label="Visit date *" name="visit_date" type="date" wire:model="visit_date" />
            <div class="col-span-2">
                <x-textarea label="Site address" name="site_address" rows="2" wire:model="site_address" />
            </div>
            <x-input label="Purpose" name="purpose" wire:model="purpose" />
            <x-select label="Assigned to" name="assigned_to" wire:model="assigned_to">
                <option value="">Unassigned</option>
                @foreach ($assignees as $assignee)
                    <option value="{{ $assignee->id }}">{{ $assignee->name }}</option>
                @endforeach
            </x-select>
            <div class="col-span-2">
                <x-textarea label="Requirements" name="requirements" rows="3" wire:model="requirements" />
            </div>
            <div class="col-span-2">
                <x-textarea label="Notes" name="notes" rows="2" wire:model="notes" />
            </div>
        </div>

        {{-- Repeating room/area rows — an arbitrary number of rows with
             several fields each doesn't fit the single-field <x-input>
             label+error contract cleanly (same reasoning Phase 15a used
             for Clients' contact list), so this stays hand-rolled, just
             reskinned with the ink/accent/panel tokens. --}}
        <div class="panel p-6">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-ink-700">Rooms / Areas</h2>
                <button type="button" wire:click="addRoom" class="text-sm text-accent-600 hover:underline">+ Add room</button>
            </div>

            <div class="space-y-3">
                @foreach ($rooms as $index => $room)
                    <div wire:key="room-{{ $index }}" class="grid grid-cols-1 md:grid-cols-12 gap-2 rounded-lg border border-ink-100 p-3">
                        <div class="col-span-3">
                            <input wire:model="rooms.{{ $index }}.area_name" type="text" placeholder="Room / area name *" class="field-input">
                            @error("rooms.{$index}.area_name") <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="col-span-2">
                            <input wire:model="rooms.{{ $index }}.length" type="number" step="0.01" min="0" placeholder="Length" class="field-input">
                        </div>
                        <div class="col-span-2">
                            <input wire:model="rooms.{{ $index }}.width" type="number" step="0.01" min="0" placeholder="Width" class="field-input">
                        </div>
                        <div class="col-span-2">
                            <input wire:model="rooms.{{ $index }}.height" type="number" step="0.01" min="0" placeholder="Height" class="field-input">
                        </div>
                        <div class="col-span-2">
                            <input wire:model="rooms.{{ $index }}.notes" type="text" placeholder="Notes" class="field-input">
                        </div>
                        <div class="col-span-1 flex items-center justify-end">
                            <button type="button" wire:click="removeRoom({{ $index }})" class="text-sm text-danger-600 hover:text-danger-700">Remove</button>
                        </div>
                    </div>
                @endforeach
            </div>
            <p class="mt-3 text-xs text-ink-400">Area is calculated automatically from length × width.</p>
        </div>

        <div class="panel p-6">
            <h2 class="mb-4 text-sm font-semibold text-ink-700">Photos</h2>
            <input wire:model="photos" type="file" multiple accept="image/*" class="block w-full text-sm text-ink-600 file:mr-4 file:rounded-lg file:border-0 file:bg-accent-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-accent-700 hover:file:bg-accent-100">
            @error('photos.*') <p class="form-error">{{ $message }}</p> @enderror
            <div wire:loading wire:target="photos" class="mt-2 text-xs text-ink-400">Uploading…</div>
        </div>

        <div class="flex items-center gap-3">
            <x-button type="submit">{{ $siteVisit ? 'Save changes' : 'Create site visit' }}</x-button>
            <x-button variant="secondary" href="{{ route('site-visits.index') }}" wire:navigate>Cancel</x-button>
        </div>
    </form>
</div>
