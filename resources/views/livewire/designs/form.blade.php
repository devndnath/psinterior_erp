<div>
    <x-breadcrumbs :items="['Designs' => route('designs.index'), ($design ? 'Edit' : 'New') => null]" class="mb-4" />

    <h1 class="mb-6 text-2xl font-semibold text-gray-900">
        {{ $design ? "Edit Design {$design->design_no}" : 'New Design' }}
    </h1>

    <form wire:submit="save" class="max-w-3xl space-y-6">
        <div class="card grid grid-cols-1 sm:grid-cols-2 gap-4 p-6">
            <div class="col-span-2">
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
                <label class="form-label">Title *</label>
                <input wire:model="title" type="text" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('title') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Category</label>
                <select wire:model="category" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                    <option value="">None</option>
                    @foreach ($categories as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('category') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div class="col-span-2">
                <label class="form-label">Description</label>
                <textarea wire:model="description" rows="3" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"></textarea>
                @error('description') <p class="form-error">{{ $message }}</p> @enderror
            </div>
        </div>

        @unless ($design)
            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">First revision</h2>
                <label class="form-label">File *</label>
                <input wire:model="file" type="file" class="block w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-brand-700 hover:file:bg-brand-100">
                @error('file') <p class="form-error">{{ $message }}</p> @enderror
                <div wire:loading wire:target="file" class="mt-2 text-xs text-gray-400">Uploading…</div>
                <p class="mt-3 text-xs text-gray-400">Every design needs a first file — later revisions are added from the design's page.</p>
            </div>
        @endunless

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary">{{ $design ? 'Save changes' : 'Create design' }}</button>
            <a href="{{ $design ? route('designs.show', $design) : route('designs.index') }}" wire:navigate class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
