<div>
    <x-page-header title="Audit Log" description="A read-only trail of create, update and delete actions across the system.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Audit Log' => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    <div class="card mb-4 flex flex-wrap items-end gap-4 p-4">
        <x-input label="From" name="from" type="date" wire:model.live="from" />
        <x-input label="To" name="to" type="date" wire:model.live="to" />
        <div class="min-w-[220px]">
            <x-select label="Subject type" name="subject_type" wire:model.live="subjectType">
                <option value="">Any type</option>
                @foreach ($subjectTypes as $type)
                    <option value="{{ $type }}">{{ class_basename($type) }}</option>
                @endforeach
            </x-select>
        </div>
        <button type="button" wire:click="resetFilters" class="btn-secondary">Reset</button>
    </div>

    <x-table :empty="$logs->isEmpty()">
        @if ($logs->isEmpty())
            <x-empty-state
                title="No activity yet"
                description="Create, update and delete actions taken across the system will appear here."
            />
        @else
            <thead>
                <tr>
                    <th>When</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Subject</th>
                    <th>Description</th>
                    <th>IP address</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($logs as $log)
                    <tr wire:key="activity-log-{{ $log->id }}">
                        <td>{{ $log->created_at?->format('d M, Y H:i') }}</td>
                        <td>{{ $log->causer?->name ?? 'System' }}</td>
                        <td class="capitalize">{{ $log->action }}</td>
                        <td>{{ class_basename($log->subject_type) }} #{{ $log->subject_id }}</td>
                        <td>{{ $log->description }}</td>
                        <td class="font-mono text-xs">{{ $log->ip_address }}</td>
                    </tr>
                @endforeach
            </tbody>
        @endif
    </x-table>

    @if (! $logs->isEmpty())
        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    @endif
</div>
