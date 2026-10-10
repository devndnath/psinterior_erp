<div>
    <x-breadcrumbs :items="['Service Requests' => route('client.service-requests.index'), 'New' => null]" class="mb-4" />

    <div class="card max-w-xl p-6">
        <form wire:submit="save" class="space-y-4">
            <div>
                <label class="form-label">Project / Warranty *</label>
                <select wire:model="warrantyId" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                    <option value="">Select…</option>
                    @foreach ($warranties as $warranty)
                        <option value="{{ $warranty->id }}">{{ $warranty->project?->name }} ({{ $warranty->start_date?->format('d M, Y') }} – {{ $warranty->end_date?->format('d M, Y') }})</option>
                    @endforeach
                </select>
                @error('warrantyId') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Complaint *</label>
                <textarea wire:model="complaint" rows="4" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"></textarea>
                @error('complaint') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="btn-primary">Submit</button>
        </form>
    </div>
</div>
