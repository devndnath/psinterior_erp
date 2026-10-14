<div>
    <x-page-header :title="$inspection ? 'Edit Inspection' : 'New Inspection'">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Inspections' => route('inspections.index'), ($inspection ? 'Edit' : 'New') => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    <form wire:submit="save" class="max-w-4xl space-y-6">
        <div class="panel grid grid-cols-1 sm:grid-cols-2 gap-4 p-6">
            <x-select label="Project *" name="project_id" wire:model="project_id" :disabled="(bool) $inspection">
                <option value="">Select…</option>
                @foreach ($projects as $proj)
                    <option value="{{ $proj->id }}">{{ $proj->project_code }} — {{ $proj->name }}</option>
                @endforeach
            </x-select>
            <x-input label="Area" name="area" wire:model="area" />
            <x-input label="Inspection Date *" name="inspection_date" type="date" wire:model="inspection_date" />
            <x-select label="Inspector" name="inspector_id" wire:model="inspector_id">
                <option value="">Select…</option>
                @foreach ($inspectors as $inspector)
                    <option value="{{ $inspector->id }}">{{ $inspector->name }}</option>
                @endforeach
            </x-select>
            <div class="col-span-2">
                <x-textarea label="Notes" name="notes" rows="2" wire:model="notes" />
            </div>
            <div class="col-span-2">
                <x-select label="Overall Result Override" name="result_override" wire:model="result_override">
                    <option value="">Compute automatically from checklist items (worst-of)</option>
                    @foreach ($results as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-select>
            </div>
        </div>

        {{-- Repeating checklist-item rows — several fields per row doesn't
             fit the single-field <x-input> label+error contract cleanly
             (same reasoning Phase 15b used for Site Visits' room list), so
             this stays hand-rolled, just reskinned with the ink/accent/panel
             tokens. --}}
        <div class="panel p-6">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-ink-700">Checklist Items</h2>
                <button type="button" wire:click="addItem" class="text-sm text-accent-600 hover:underline">+ Add item</button>
            </div>

            <div class="space-y-3">
                @foreach ($items as $index => $item)
                    <div wire:key="insp-item-{{ $index }}" class="grid grid-cols-1 md:grid-cols-12 gap-2 rounded-lg border border-ink-100 p-3">
                        <div class="col-span-6">
                            <input wire:model="items.{{ $index }}.checklist_item" type="text" placeholder="Checklist item *" class="field-input">
                            @error("items.{$index}.checklist_item") <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="col-span-3">
                            <select wire:model="items.{{ $index }}.result" class="field-input">
                                @foreach ($results as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-2">
                            <input wire:model="items.{{ $index }}.notes" type="text" placeholder="Notes" class="field-input">
                        </div>
                        <div class="col-span-1 flex items-center justify-end">
                            <button type="button" wire:click="removeItem({{ $index }})" class="text-sm text-danger-600 hover:text-danger-700">✕</button>
                        </div>
                    </div>
                @endforeach
            </div>
            <p class="mt-3 text-xs text-ink-400">The inspection's overall result is the worst of its checklist items (Failed &gt; Requires Correction &gt; Passed) unless you choose an explicit override above.</p>
        </div>

        <div class="flex items-center gap-3">
            <x-button type="submit">{{ $inspection ? 'Save changes' : 'Create inspection' }}</x-button>
            <x-button variant="secondary" href="{{ $inspection ? route('inspections.show', $inspection) : route('inspections.index') }}" wire:navigate>Cancel</x-button>
        </div>
    </form>
</div>
