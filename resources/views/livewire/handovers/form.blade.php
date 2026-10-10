<div>
    <x-breadcrumbs :items="[$project->name => route('projects.show', $project), 'Record Handover' => null]" class="mb-4" />

    <h1 class="mb-6 text-2xl font-semibold text-gray-900">Record Handover</h1>

    <form wire:submit="save" class="max-w-2xl space-y-6">
        <div class="card grid grid-cols-2 gap-4 p-6">
            <div class="col-span-2">
                <label class="form-label">Handover Date *</label>
                <input wire:model="handover_date" type="date" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('handover_date') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div class="col-span-2">
                <label class="form-label">Notes</label>
                <textarea wire:model="notes" rows="3" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"></textarea>
                @error('notes') <p class="form-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary">Record Handover</button>
            <a href="{{ route('projects.show', $project) }}" wire:navigate class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
