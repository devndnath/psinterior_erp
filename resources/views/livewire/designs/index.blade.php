<div>
    {{-- Phase 16: replaced the hand-rolled <h1> (duplicated by
         layouts.admin's old fallback heading, now removed) with the
         same <x-page-header> every other redesigned Index view uses. --}}
    <x-page-header title="Designs">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Designs' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @can('create', \App\Models\Design::class)
                <a href="{{ route('designs.create') }}" wire:navigate class="btn-primary">+ New Design</a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="card mb-4 flex flex-wrap items-end gap-4 p-4">
        <div class="min-w-[220px] flex-1">
            <label class="form-label">Search</label>
            <input wire:model.live.debounce.400ms="search" type="text" placeholder="Design no, title…" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
        </div>
        <div>
            <label class="form-label">Project</label>
            <select wire:model.live="project" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                <option value="">Any project</option>
                @foreach ($projects as $proj)
                    <option value="{{ $proj->id }}">{{ $proj->project_code }} — {{ $proj->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Category</label>
            <select wire:model.live="category" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                <option value="">Any category</option>
                @foreach ($categories as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Status</label>
            <select wire:model.live="status" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                <option value="">Any status</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button type="button" wire:click="resetFilters" class="btn-secondary">Reset</button>
    </div>

    <div class="card overflow-hidden">
        @if ($designs->isEmpty())
            <x-empty-state
                title="No designs found"
                description="Upload a design to start tracking revisions and client approvals for a project."
            />
        @else
            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Design #</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Title</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Project</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Category</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Revisions</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach ($designs as $design)
                        <tr wire:key="design-{{ $design->id }}" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-mono text-gray-500">
                                <a href="{{ route('designs.show', $design) }}" wire:navigate class="text-brand-600 hover:underline">{{ $design->design_no }}</a>
                            </td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $design->title }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                @if ($design->project)
                                    <a href="{{ route('projects.show', $design->project) }}" wire:navigate class="hover:underline">{{ $design->project->project_code }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $design->category?->label() ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $design->revisions->count() }}</td>
                            <td class="px-4 py-3 text-sm">
                                <x-status-badge :label="$design->status->label()" :color="$design->status->color()" />
                            </td>
                            <td class="px-4 py-3 text-right text-sm">
                                <a href="{{ route('designs.show', $design) }}" wire:navigate class="text-gray-500 hover:text-gray-700">View</a>
                                @can('update', $design)
                                    <a href="{{ route('designs.edit', $design) }}" wire:navigate class="ml-3 text-gray-500 hover:text-gray-700">Edit</a>
                                @endcan
                                @can('delete', $design)
                                    <span class="ml-3 inline-block">
                                        <x-confirm-dialog
                                            title="Delete this design?"
                                            :message='"This will remove {$design->design_no} and its revisions. This cannot be undone."'
                                            action="deleteDesign"
                                            :params="[$design->id]"
                                        >
                                            <x-slot:trigger>
                                                <button type="button" class="text-red-500 hover:text-red-700">Delete</button>
                                            </x-slot:trigger>
                                        </x-confirm-dialog>
                                    </span>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
            <div class="border-t border-gray-100 px-4 py-3">
                {{ $designs->links() }}
            </div>
        @endif
    </div>
</div>
