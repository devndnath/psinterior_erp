<div>
    <x-page-header title="New Service Request">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Service Requests' => route('client.service-requests.index'), 'New' => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    <div class="panel max-w-xl p-6">
        <form wire:submit="save" class="space-y-4">
            <x-select label="Project / Warranty *" name="warrantyId" wire:model="warrantyId">
                <option value="">Select…</option>
                @foreach ($warranties as $warranty)
                    <option value="{{ $warranty->id }}">{{ $warranty->project?->name }} ({{ $warranty->start_date?->format('d M, Y') }} – {{ $warranty->end_date?->format('d M, Y') }})</option>
                @endforeach
            </x-select>

            <x-textarea label="Complaint *" name="complaint" rows="4" wire:model="complaint" />

            <x-button type="submit">Submit</x-button>
        </form>
    </div>
</div>
