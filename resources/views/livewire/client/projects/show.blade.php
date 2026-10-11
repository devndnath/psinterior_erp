<div>
    <x-page-header :title="$project->name">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Projects' => route('client.projects.index'), $project->project_code => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            <x-status-badge :label="$project->status->label()" :color="$project->status->color()" />
        </x-slot:actions>
    </x-page-header>

    <p class="-mt-4 mb-6 text-sm text-ink-500">
        <span class="font-mono">{{ $project->project_code }}</span>
        &middot; Project Manager: {{ $project->projectManager?->name ?? '—' }}
    </p>

    <div class="mb-6 panel p-6">
        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Overall Progress</h2>
        <x-progress-bar :value="$project->progress" :show-label="false" />
        <p class="mt-2 text-sm text-ink-500">{{ $project->progress }}% complete</p>
    </div>

    <div class="panel p-6">
        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Milestones</h2>
        @if ($project->milestones->isEmpty())
            <p class="text-sm text-ink-500">No milestones recorded yet.</p>
        @else
            <ul class="space-y-2">
                @foreach ($project->milestones as $milestone)
                    <li class="flex items-center justify-between rounded-lg border border-ink-100 p-3 text-sm">
                        <span class="font-medium text-ink-900">{{ $milestone->name }}</span>
                        <span class="text-ink-500">{{ $milestone->status->label() }} &middot; {{ $milestone->progress }}%</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
