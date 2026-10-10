<div>
    <x-breadcrumbs :items="['Projects' => route('client.projects.index'), $project->project_code => null]" class="mb-4" />

    <div class="mb-6">
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-semibold text-gray-900">{{ $project->name }}</h1>
            <x-status-badge :label="$project->status->label()" :color="$project->status->color()" />
        </div>
        <p class="mt-1 text-sm text-gray-500">{{ $project->project_code }} &middot; Project Manager: {{ $project->projectManager?->name ?? '—' }}</p>
    </div>

    <div class="mb-6 card p-6">
        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Overall Progress</h2>
        <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100">
            <div class="h-2 rounded-full bg-brand-600" style="width: {{ $project->progress }}%"></div>
        </div>
        <p class="mt-2 text-sm text-gray-500">{{ $project->progress }}% complete</p>
    </div>

    <div class="card p-6">
        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Milestones</h2>
        <ul class="space-y-2">
            @forelse ($project->milestones as $milestone)
                <li class="flex items-center justify-between rounded-lg border border-gray-100 p-3 text-sm">
                    <span class="font-medium text-gray-900">{{ $milestone->name }}</span>
                    <span class="text-gray-500">{{ $milestone->status->label() }} &middot; {{ $milestone->progress }}%</span>
                </li>
            @empty
                <p class="text-sm text-gray-400">No milestones recorded yet.</p>
            @endforelse
        </ul>
    </div>
</div>
