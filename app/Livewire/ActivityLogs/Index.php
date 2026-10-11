<?php

namespace App\Livewire\ActivityLogs;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Phase 14: read-only audit trail viewer (ARCHITECTURE.md §49/§78 rule 6).
 * Gated on ActivityLogPolicy::viewAny, which checks the existing
 * `view_reports` ability — there is no dedicated `view_audit_log`
 * permission string anywhere in this app, and the Policy's own docblock
 * is explicit that only Super Admin (via the Gate::before bypass) may
 * ever write or delete a row here; any user who can see the Reports hub
 * may read this trail.
 */
#[Layout('layouts.admin', ['title' => 'Audit Log'])]
#[Title('Audit Log')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $from = '';

    #[Url]
    public string $to = '';

    #[Url]
    public string $subjectType = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', ActivityLog::class);
    }

    public function updating($property): void
    {
        if (in_array($property, ['from', 'to', 'subjectType'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['from', 'to', 'subjectType']);
    }

    public function render()
    {
        $logs = ActivityLog::query()
            ->with(['causer'])
            ->when($this->from, fn ($q) => $q->whereDate('created_at', '>=', $this->from))
            ->when($this->to, fn ($q) => $q->whereDate('created_at', '<=', $this->to))
            ->when($this->subjectType, fn ($q) => $q->where('subject_type', $this->subjectType))
            ->latest('created_at')
            ->paginate(20);

        return view('livewire.activity-logs.index', [
            'logs' => $logs,
            // Distinct subject types already in the table, shown by their
            // short class name — cheap (no extra table, no join) and
            // avoids guessing at a fixed list of every loggable model.
            'subjectTypes' => ActivityLog::query()
                ->select('subject_type')
                ->distinct()
                ->orderBy('subject_type')
                ->pluck('subject_type'),
        ]);
    }
}
