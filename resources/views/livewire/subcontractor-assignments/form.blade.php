<div>
    <x-breadcrumbs :items="['New Assignment' => null]" class="mb-4" />

    <h1 class="mb-6 text-2xl font-semibold text-gray-900">New Subcontractor Assignment</h1>

    <form wire:submit="save" class="max-w-2xl space-y-6">
        <div class="card grid grid-cols-2 gap-4 p-6">
            <div>
                <label class="form-label">Subcontractor *</label>
                <select wire:model="subcontractor_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                    <option value="">Select…</option>
                    @foreach ($subcontractors as $sub)
                        <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                    @endforeach
                </select>
                @error('subcontractor_id') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Project *</label>
                <select wire:model="project_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                    <option value="">Select…</option>
                    @foreach ($projects as $proj)
                        <option value="{{ $proj->id }}">{{ $proj->project_code }} — {{ $proj->name }}</option>
                    @endforeach
                </select>
                @error('project_id') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div class="col-span-2">
                <label class="form-label">Work Description</label>
                <textarea wire:model="work_description" rows="3" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"></textarea>
                @error('work_description') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Rate *</label>
                <input wire:model="rate" type="number" step="0.01" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('rate') <p class="form-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary">Create assignment</button>
            <a href="{{ url()->previous() }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
