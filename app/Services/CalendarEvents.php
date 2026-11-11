<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\QuotationStatus;
use App\Models\Invoice;
use App\Models\LeadFollowup;
use App\Models\Meeting;
use App\Models\ProjectMilestone;
use App\Models\ProjectTask;
use App\Models\Quotation;
use App\Models\SiteVisit;
use App\Models\Warranty;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Everything with a date, for one person, in a date range: milestones,
 * tasks, site visits, meetings, scheduled calls, invoice due dates,
 * quotation and warranty expiry. Each source is gated on the permission
 * its own module uses, and read from the model that owns it (no copies).
 *
 * Used by the Calendar page (a month) and the top bar's schedule popup
 * (the next few days), so both always show the same things.
 */
class CalendarEvents
{
    /**
     * @return Collection<int, array{date: string, time: ?string, type: string, title: string, subtitle: ?string, url: ?string, color: string}>
     */
    public function between(User $user, string $from, string $to, ?int $project = null): Collection
    {
        $events = collect();

        if ($user->can('view_milestones')) {
            $events = $events->merge(
                ProjectMilestone::query()
                    ->with('project')
                    ->whereBetween('due_date', [$from, $to])
                    ->when($project, fn ($q) => $q->where('project_id', $project))
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
                    ->whereBetween('due_date', [$from, $to])
                    ->when($project, fn ($q) => $q->where('project_id', $project))
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
                    ->whereBetween('visit_date', [$from, $to])
                    ->when($project, fn ($q) => $q->where('project_id', $project))
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
                    ->whereBetween('date', [$from, $to])
                    ->when($project, fn ($q) => $q->where('project_id', $project))
                    ->get()
                    ->map(fn (Meeting $m) => $this->event(
                        $m->date->toDateString(),
                        'Meeting',
                        $m->type?->label() ?? 'Meeting',
                        $m->project?->project_code,
                        route('meetings.show', $m),
                        'green',
                        $m->time,
                    ))
            );
        }

        // Scheduled calls (leads and clients) — not tied to a project, so
        // hidden while a project filter is on.
        if (! $project && ($user->can('view_leads') || $user->can('view_clients'))) {
            $events = $events->merge(
                LeadFollowup::query()->visibleTo($user)->calls()->toMake()
                    ->with(LeadFollowup::CONTACT_RELATIONS)
                    ->whereBetween('date', [$from, $to])
                    ->get()
                    ->map(fn (LeadFollowup $c) => $this->event(
                        $c->date->toDateString(),
                        'Call',
                        $c->type->label().' · '.($c->contactName() ?? '—'),
                        $c->time ? substr($c->time, 0, 5) : $c->contactKind(),
                        route('calls.index', [$c->client_id ? 'client' : 'lead' => $c->client_id ?: $c->lead_id]),
                        'teal',
                        $c->time,
                    ))
            );
        }

        if ($user->can('view_invoices')) {
            $events = $events->merge(
                Invoice::query()
                    ->with('project')
                    ->whereBetween('due_date', [$from, $to])
                    ->whereIn('status', [
                        InvoiceStatus::Issued->value,
                        InvoiceStatus::PartiallyPaid->value,
                        InvoiceStatus::Overdue->value,
                    ])
                    ->when($project, fn ($q) => $q->where('project_id', $project))
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
                    ->whereBetween('valid_until', [$from, $to])
                    ->whereIn('status', [
                        QuotationStatus::Sent->value,
                        QuotationStatus::Viewed->value,
                        QuotationStatus::Negotiation->value,
                    ])
                    ->when($project, fn ($q) => $q->where('project_id', $project))
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
                    ->whereBetween('end_date', [$from, $to])
                    ->when($project, fn ($q) => $q->where('project_id', $project))
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

        return $events;
    }

    protected function event(string $date, string $type, string $title, ?string $subtitle, ?string $url, string $color, ?string $time = null): array
    {
        return [
            // "HH:MM" when the source has a time of day (meetings, calls).
            'time' => $time ? substr($time, 0, 5) : null,
            'date' => $date,
            'type' => $type,
            'title' => $title,
            'subtitle' => $subtitle,
            'url' => $url,
            'color' => $color,
        ];
    }
}
