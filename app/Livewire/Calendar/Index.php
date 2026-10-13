<?php

namespace App\Livewire\Calendar;

use App\Enums\InvoiceStatus;
use App\Enums\QuotationStatus;
use App\Models\Invoice;
use App\Models\Meeting;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\ProjectTask;
use App\Models\Quotation;
use App\Models\SiteVisit;
use App\Models\Warranty;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Closes the "nothing shows what's happening/due across projects on any
 * given day" audit gap — not a Gantt chart (spec explicitly stayed away
 * from that scope), just a month view that pulls together dates that
 * already exist on seven different modules: milestone/task due dates,
 * site visits, meetings, invoice due dates, quotation expiry, and
 * warranty expiry. No new table — every row here is read from the model
 * that already owns it, so this view can never drift out of sync with
 * (or duplicate) those modules' own records.
 *
 * Each source is gated on the exact same permission its own module's
 * index page uses (`view_milestones`, `view_invoices`, ...), the same
 * "a card simply doesn't appear" pattern Dashboard\Index uses — not a
 * single invented `view_calendar` ability gating the whole page, since
 * a Designer and an Accountant should see different things here.
 */
#[Layout('layouts.admin', ['title' => 'Calendar'])]
#[Title('Calendar')]
class Index extends Component
{
    #[Url]
    public string $month = '';

    #[Url]
    public string $project = '';

    public function mount(): void
    {
        if ($this->month === '' || ! preg_match('/^\d{4}-\d{2}$/', $this->month)) {
            $this->month = now()->format('Y-m');
        }
    }

    public function previousMonth(): void
    {
        $this->month = $this->monthStart()->subMonthNoOverflow()->format('Y-m');
    }

    public function nextMonth(): void
    {
        $this->month = $this->monthStart()->addMonthNoOverflow()->format('Y-m');
    }

    public function goToToday(): void
    {
        $this->month = now()->format('Y-m');
    }

    /**
     * `createFromFormat('Y-m', ...)` alone fills in the unspecified day
     * with *today's* day-of-month (a PHP DateTime quirk, not a Carbon
     * one) — parsing "2026-11" on the 31st overflows into December
     * because November has no 31st. Pinning the day to 1 up front avoids
     * that entirely, rather than relying on every caller remembering to
     * call ->startOfMonth() immediately afterward.
     */
    protected function monthStart(): CarbonImmutable
    {
        return CarbonImmutable::createFromFormat('Y-m-d', $this->month.'-01')->startOfMonth();
    }

    public function updatingProject(): void
    {
        // No resetPage() — this view isn't paginated, it's a fixed grid
        // for the selected month.
    }

    public function render()
    {
        $user = Auth::user();
        $monthStart = $this->monthStart();
        $monthEnd = $monthStart->endOfMonth();

        $events = collect();

        if ($user->can('view_milestones')) {
            $events = $events->merge(
                ProjectMilestone::query()
                    ->with('project')
                    ->whereBetween('due_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                    ->when($this->project, fn ($q) => $q->where('project_id', $this->project))
                    ->get()
                    ->map(fn (ProjectMilestone $m) => $this->event(
                        $m->due_date->toDateString(),
                        'Milestone',
                        $m->name,
                        $m->project?->project_code,
                        $m->project ? route('projects.show', $m->project) : null,
                        'purple',
                    ))
            );
        }

        if ($user->can('view_tasks')) {
            $events = $events->merge(
                ProjectTask::query()
                    ->with('project')
                    ->whereBetween('due_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                    ->when($this->project, fn ($q) => $q->where('project_id', $this->project))
                    ->get()
                    ->map(fn (ProjectTask $t) => $this->event(
                        $t->due_date->toDateString(),
                        'Task',
                        $t->name,
                        $t->project?->project_code,
                        $t->project ? route('projects.show', $t->project) : null,
                        'blue',
                    ))
            );
        }

        if ($user->can('view_site_visits')) {
            $events = $events->merge(
                SiteVisit::query()
                    ->with(['project', 'client'])
                    ->whereBetween('visit_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                    ->when($this->project, fn ($q) => $q->where('project_id', $this->project))
                    ->get()
                    ->map(fn (SiteVisit $v) => $this->event(
                        $v->visit_date->toDateString(),
                        'Site Visit',
                        $v->client?->name ?? $v->site_address,
                        $v->project?->project_code,
                        route('site-visits.show', $v),
                        'indigo',
                    ))
            );
        }

        if ($user->can('view_meetings')) {
            $events = $events->merge(
                Meeting::query()
                    ->with('project')
                    ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                    ->when($this->project, fn ($q) => $q->where('project_id', $this->project))
                    ->get()
                    ->map(fn (Meeting $m) => $this->event(
                        $m->date->toDateString(),
                        'Meeting',
                        $m->type?->label() ?? 'Meeting',
                        $m->project?->project_code,
                        route('meetings.show', $m),
                        'green',
                    ))
            );
        }

        if ($user->can('view_invoices')) {
            $events = $events->merge(
                Invoice::query()
                    ->with('project')
                    ->whereBetween('due_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                    ->whereIn('status', [
                        InvoiceStatus::Issued->value,
                        InvoiceStatus::PartiallyPaid->value,
                        InvoiceStatus::Overdue->value,
                    ])
                    ->when($this->project, fn ($q) => $q->where('project_id', $this->project))
                    ->get()
                    ->map(fn (Invoice $i) => $this->event(
                        $i->due_date->toDateString(),
                        'Invoice Due',
                        $i->invoice_no,
                        $i->project?->project_code,
                        route('invoices.show', $i),
                        'red',
                    ))
            );
        }

        if ($user->can('view_quotations')) {
            $events = $events->merge(
                Quotation::query()
                    ->with('project')
                    ->whereBetween('valid_until', [$monthStart->toDateString(), $monthEnd->toDateString()])
                    ->whereIn('status', [
                        QuotationStatus::Sent->value,
                        QuotationStatus::Viewed->value,
                        QuotationStatus::Negotiation->value,
                    ])
                    ->when($this->project, fn ($q) => $q->where('project_id', $this->project))
                    ->get()
                    ->map(fn (Quotation $q) => $this->event(
                        $q->valid_until->toDateString(),
                        'Quotation Expiry',
                        $q->quotation_no,
                        $q->project?->project_code,
                        route('quotations.show', $q),
                        'yellow',
                    ))
            );
        }

        if ($user->can('view_warranties')) {
            $events = $events->merge(
                Warranty::query()
                    ->with('project')
                    ->whereBetween('end_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                    ->when($this->project, fn ($q) => $q->where('project_id', $this->project))
                    ->get()
                    ->map(fn (Warranty $w) => $this->event(
                        $w->end_date->toDateString(),
                        'Warranty Expiry',
                        $w->project?->project_code ?? 'Warranty',
                        $w->project?->project_code,
                        route('warranties.show', $w),
                        'gray',
                    ))
            );
        }

        /** @var Collection<string, Collection> $eventsByDate */
        $eventsByDate = $events->groupBy('date');

        return view('livewire.calendar.index', [
            'monthStart' => $monthStart,
            'monthEnd' => $monthEnd,
            'eventsByDate' => $eventsByDate,
            'projects' => $user->can('view_projects')
                ? Project::orderBy('name')->get(['id', 'name', 'project_code'])
                : collect(),
        ]);
    }

    protected function event(string $date, string $type, string $title, ?string $subtitle, ?string $url, string $color): array
    {
        return [
            'date' => $date,
            'type' => $type,
            'title' => $title,
            'subtitle' => $subtitle,
            'url' => $url,
            'color' => $color,
        ];
    }
}
