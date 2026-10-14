<div>
    <x-page-header :title="$meeting ? 'Edit Meeting' : 'New Meeting'">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Meetings' => route('meetings.index'), ($meeting ? 'Edit' : 'New') => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    <form wire:submit="save" class="max-w-4xl space-y-6">
        <div class="panel grid grid-cols-1 sm:grid-cols-2 gap-4 p-6">
            <x-select label="Project" name="project_id" wire:model="project_id">
                <option value="">No project</option>
                @foreach ($projects as $proj)
                    <option value="{{ $proj->id }}">{{ $proj->project_code }} — {{ $proj->name }}</option>
                @endforeach
            </x-select>
            <x-select label="Type *" name="type" wire:model="type">
                <option value="">Select…</option>
                @foreach ($types as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-select>
            <x-input label="Date *" name="date" type="date" wire:model="date" />
            <x-input label="Time" name="time" placeholder="e.g. 10:00 AM" wire:model="time" />
            <div class="col-span-2">
                <x-input label="Location" name="location" wire:model="location" />
            </div>
            <div class="col-span-2">
                <x-textarea label="Agenda" name="agenda" rows="2" wire:model="agenda" />
            </div>
            <div class="col-span-2">
                <x-textarea label="Discussion" name="discussion" rows="2" wire:model="discussion" />
            </div>
            <div class="col-span-2">
                <x-textarea label="Decisions" name="decisions" rows="2" wire:model="decisions" />
            </div>
            <div class="col-span-2">
                <x-textarea label="Follow-up" name="follow_up" rows="2" wire:model="follow_up" />
            </div>
        </div>

        <div class="panel p-6">
            <h2 class="mb-4 text-sm font-semibold text-ink-700">Participants</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <x-select label="Users" name="participant_users" wire:model="participant_users" multiple size="6">
                    @foreach ($users as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </x-select>
                <x-select label="Clients" name="participant_clients" wire:model="participant_clients" multiple size="6">
                    @foreach ($clients as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </x-select>
                <x-select label="Vendors" name="participant_vendors" wire:model="participant_vendors" multiple size="6">
                    @foreach ($vendors as $v)
                        <option value="{{ $v->id }}">{{ $v->company_name }}</option>
                    @endforeach
                </x-select>
            </div>
            <p class="mt-3 text-xs text-ink-400">Hold Ctrl/Cmd to select multiple.</p>
        </div>

        <div class="flex items-center gap-3">
            <x-button type="submit">{{ $meeting ? 'Save changes' : 'Create meeting' }}</x-button>
            <x-button variant="secondary" href="{{ $meeting ? route('meetings.show', $meeting) : route('meetings.index') }}" wire:navigate>Cancel</x-button>
        </div>
    </form>
</div>
