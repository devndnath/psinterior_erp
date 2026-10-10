<div>
    <x-breadcrumbs :items="['Inspections' => route('inspections.index'), ($inspection ? 'Edit' : 'New') => null]" class="mb-4" />

    <h1 class="mb-6 text-2xl font-semibold text-gray-900">
        {{ $inspection ? 'Edit Inspection' : 'New Inspection' }}
    </h1>

    <form wire:submit="save" class="max-w-4xl space-y-6">
        <div class="card grid grid-cols-2 gap-4 p-6">
            <div>
                <label class="form-label">Project *</label>
                <select wire:model="project_id" @disabled($inspection) class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm disabled:bg-gray-50">
                    <option value="">Select…</option>
                    @foreach ($projects as $proj)
                        <option value="{{ $proj->id }}">{{ $proj->project_code }} — {{ $proj->name }}</option>
                    @endforeach
                </select>
                @error('project_id') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Area</label>
                <input wire:model="area" type="text" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('area') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Inspection Date *</label>
                <input wire:model="inspection_date" type="date" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('inspection_date') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Inspector</label>
                <select wire:model="inspector_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                    <option value="">Select…</option>
                    @foreach ($inspectors as $inspector)
                        <option value="{{ $inspector->id }}">{{ $inspector->name }}</option>
                    @endforeach
                </select>
                @error('inspector_id') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div class="col-span-2">
                <label class="form-label">Notes</label>
                <textarea wire:model="notes" rows="2" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"></textarea>
                @error('notes') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div class="col-span-2">
                <label class="form-label">Overall Result Override</label>
                <select wire:model="result_override" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                    <option value="">Compute automatically from checklist items (worst-of)</option>
                    @foreach ($results as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('result_override') <p class="form-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="card p-6">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Checklist Items</h2>
                <button type="button" wire:click="addItem" class="text-sm text-brand-600 hover:underline">+ Add item</button>
            </div>

            <div class="space-y-3">
                @foreach ($items as $index => $item)
                    <div wire:key="insp-item-{{ $index }}" class="grid grid-cols-12 gap-2 rounded-lg border border-gray-100 p-3">
                        <div class="col-span-6">
                            <input wire:model="items.{{ $index }}.checklist_item" type="text" placeholder="Checklist item *" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                            @error("items.{$index}.checklist_item") <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="col-span-3">
                            <select wire:model="items.{{ $index }}.result" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                                @foreach ($results as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-2">
                            <input wire:model="items.{{ $index }}.notes" type="text" placeholder="Notes" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                        </div>
                        <div class="col-span-1 flex items-center justify-end">
                            <button type="button" wire:click="removeItem({{ $index }})" class="text-sm text-red-500 hover:text-red-700">✕</button>
                        </div>
                    </div>
                @endforeach
            </div>
            <p class="mt-3 text-xs text-gray-400">The inspection's overall result is the worst of its checklist items (Failed &gt; Requires Correction &gt; Passed) unless you choose an explicit override above.</p>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary">{{ $inspection ? 'Save changes' : 'Create inspection' }}</button>
            <a href="{{ $inspection ? route('inspections.show', $inspection) : route('inspections.index') }}" wire:navigate class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
