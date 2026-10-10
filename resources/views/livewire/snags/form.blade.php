<div>
    <x-breadcrumbs :items="['Snags' => route('snags.index'), ($snag ? 'Edit' : 'New') => null]" class="mb-4" />

    <h1 class="mb-6 text-2xl font-semibold text-gray-900">
        {{ $snag ? "Edit Snag {$snag->snag_no}" : 'New Snag' }}
    </h1>

    <form wire:submit="save" class="max-w-2xl space-y-6">
        <div class="card grid grid-cols-2 gap-4 p-6">
            <div class="col-span-2">
                <label class="form-label">Project *</label>
                <select wire:model="project_id" @disabled($snag) class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm disabled:bg-gray-50">
                    <option value="">Select…</option>
                    @foreach ($projects as $proj)
                        <option value="{{ $proj->id }}">{{ $proj->project_code }} — {{ $proj->name }}</option>
                    @endforeach
                </select>
                @error('project_id') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            @if ($inspection)
                <div class="col-span-2 rounded-lg bg-blue-50 p-3 text-sm text-blue-700">
                    Raised from Inspection #{{ $inspection->id }} ({{ $inspection->inspection_date->format('d M, Y') }})
                </div>
            @endif
            <div class="col-span-2">
                <label class="form-label">Area</label>
                <input wire:model="area" type="text" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('area') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div class="col-span-2">
                <label class="form-label">Issue *</label>
                <textarea wire:model="issue" rows="3" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"></textarea>
                @error('issue') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div class="col-span-2">
                <label class="form-label">Deadline</label>
                <input wire:model="deadline" type="date" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('deadline') <p class="form-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary">{{ $snag ? 'Save changes' : 'Create snag' }}</button>
            <a href="{{ $snag ? route('snags.show', $snag) : route('snags.index') }}" wire:navigate class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
