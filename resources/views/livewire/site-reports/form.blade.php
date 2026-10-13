<div>
    <x-page-header :title="$siteReport ? 'Edit Site Report' : 'New Site Report'" :description="$siteReport ? 'Editing the report filed for '.$siteReport->report_date?->format('d M, Y').'.' : null">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Site Reports' => route('site-reports.index'), ($siteReport ? 'Edit' : 'New') => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    <form wire:submit="save" class="max-w-4xl space-y-6">
        <div class="panel grid grid-cols-2 gap-4 p-6">
            <x-select label="Project *" name="project_id" wire:model="project_id" :disabled="(bool) $siteReport">
                <option value="">Select…</option>
                @foreach ($projects as $proj)
                    <option value="{{ $proj->id }}">{{ $proj->project_code }} — {{ $proj->name }}</option>
                @endforeach
            </x-select>
            <x-input label="Report date *" name="report_date" type="date" wire:model="report_date" />
            <x-input label="Weather" name="weather" wire:model="weather" placeholder="e.g. Sunny, light rain in the afternoon" />
            <x-input label="Manpower on site" name="manpower_count" type="number" min="0" wire:model="manpower_count" />
            <div class="col-span-2">
                <x-textarea label="Work carried out today *" name="work_summary" rows="4" wire:model="work_summary" />
            </div>
            <div class="col-span-2">
                <x-textarea label="Materials received / used" name="materials_notes" rows="2" wire:model="materials_notes" />
            </div>
            <div class="col-span-2">
                <x-textarea label="Issues / delays" name="issues" rows="2" wire:model="issues" />
            </div>
            <div class="col-span-2">
                <x-textarea label="Notes" name="notes" rows="2" wire:model="notes" />
            </div>
        </div>

        <div class="panel p-6">
            <h2 class="mb-4 text-sm font-semibold text-ink-700">Photos</h2>
            <input wire:model="photos" type="file" multiple accept="image/*" class="block w-full text-sm text-ink-600 file:mr-4 file:rounded-lg file:border-0 file:bg-accent-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-accent-700 hover:file:bg-accent-100">
            @error('photos.*') <p class="form-error">{{ $message }}</p> @enderror
            <div wire:loading wire:target="photos" class="mt-2 text-xs text-ink-400">Uploading…</div>
        </div>

        <div class="flex items-center gap-3">
            <x-button type="submit">{{ $siteReport ? 'Save changes' : 'Create site report' }}</x-button>
            <x-button variant="secondary" href="{{ route('site-reports.index') }}" wire:navigate>Cancel</x-button>
        </div>
    </form>
</div>
