<div>
    <x-breadcrumbs :items="['Warranties' => route('warranties.index'), 'Warranty' => route('warranties.show', $warranty), 'New Service Request' => null]" class="mb-4" />

    <h1 class="mb-6 text-2xl font-semibold text-gray-900">New Service Request</h1>

    <form wire:submit="save" class="max-w-2xl space-y-6">
        <div class="card grid grid-cols-2 gap-4 p-6">
            <div class="col-span-2 rounded-lg bg-blue-50 p-3 text-sm text-blue-700">
                Against warranty for {{ $warranty->project?->project_code }} — {{ $warranty->project?->name }}
                ({{ $warranty->start_date->format('d M, Y') }} – {{ $warranty->end_date->format('d M, Y') }})
            </div>
            <div class="col-span-2">
                <label class="form-label">Client *</label>
                <select wire:model="client_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                    <option value="">Select…</option>
                    @foreach ($clients as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
                @error('client_id') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div class="col-span-2">
                <label class="form-label">Complaint *</label>
                <textarea wire:model="complaint" rows="4" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"></textarea>
                @error('complaint') <p class="form-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <p class="text-xs text-gray-400">No client portal yet — this is recorded by staff on the client's behalf.</p>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary">Create Service Request</button>
            <a href="{{ route('warranties.show', $warranty) }}" wire:navigate class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
