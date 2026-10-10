<div>
    <x-breadcrumbs :items="['Meetings' => route('meetings.index'), ($meeting ? 'Edit' : 'New') => null]" class="mb-4" />

    <h1 class="mb-6 text-2xl font-semibold text-gray-900">
        {{ $meeting ? 'Edit Meeting' : 'New Meeting' }}
    </h1>

    <form wire:submit="save" class="max-w-4xl space-y-6">
        <div class="card grid grid-cols-2 gap-4 p-6">
            <div>
                <label class="form-label">Project</label>
                <select wire:model="project_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                    <option value="">No project</option>
                    @foreach ($projects as $proj)
                        <option value="{{ $proj->id }}">{{ $proj->project_code }} — {{ $proj->name }}</option>
                    @endforeach
                </select>
                @error('project_id') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Type *</label>
                <select wire:model="type" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                    <option value="">Select…</option>
                    @foreach ($types as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('type') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Date *</label>
                <input wire:model="date" type="date" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('date') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Time</label>
                <input wire:model="time" type="text" placeholder="e.g. 10:00 AM" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('time') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div class="col-span-2">
                <label class="form-label">Location</label>
                <input wire:model="location" type="text" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('location') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div class="col-span-2">
                <label class="form-label">Agenda</label>
                <textarea wire:model="agenda" rows="2" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"></textarea>
                @error('agenda') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div class="col-span-2">
                <label class="form-label">Discussion</label>
                <textarea wire:model="discussion" rows="2" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"></textarea>
                @error('discussion') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div class="col-span-2">
                <label class="form-label">Decisions</label>
                <textarea wire:model="decisions" rows="2" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"></textarea>
                @error('decisions') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div class="col-span-2">
                <label class="form-label">Follow-up</label>
                <textarea wire:model="follow_up" rows="2" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"></textarea>
                @error('follow_up') <p class="form-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="card p-6">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Participants</h2>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="form-label">Users</label>
                    <select wire:model="participant_users" multiple size="6" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Clients</label>
                    <select wire:model="participant_clients" multiple size="6" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                        @foreach ($clients as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Vendors</label>
                    <select wire:model="participant_vendors" multiple size="6" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                        @foreach ($vendors as $v)
                            <option value="{{ $v->id }}">{{ $v->company_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <p class="mt-3 text-xs text-gray-400">Hold Ctrl/Cmd to select multiple.</p>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary">{{ $meeting ? 'Save changes' : 'Create meeting' }}</button>
            <a href="{{ $meeting ? route('meetings.show', $meeting) : route('meetings.index') }}" wire:navigate class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
