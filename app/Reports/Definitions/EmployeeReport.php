<?php

namespace App\Reports\Definitions;

use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\SiteVisit;
use App\Models\User;
use App\Reports\Filters;
use App\Reports\Report;
use Illuminate\Support\Collection;

/** CRM work per staff member: leads handled, won, follow-ups and visits. */
class EmployeeReport extends Report
{
    public static function key(): string { return 'employee-report'; }
    public static function title(): string { return 'Employee Report'; }
    public static function group(): string { return 'crm'; }
    public static function description(): string { return 'CRM work per staff member — leads assigned and won, follow-ups logged and site visits made.'; }

    public function filters(): array { return ['dates', 'user']; }
    public function defaultRange(): string { return 'year'; }

    public function note(): ?string
    {
        return 'Leads counted by the date they were created; follow-ups and site visits by their own date. Win rate = won ÷ (won + lost) of those leads.';
    }

    public function columns(): array
    {
        return [
            'staff' => ['label' => 'Staff member', 'type' => 'text'],
            'leads' => ['label' => 'Leads assigned', 'type' => 'int', 'total' => true],
            'open' => ['label' => 'Open', 'type' => 'int', 'total' => true],
            'won' => ['label' => 'Won', 'type' => 'int', 'total' => true],
            'lost' => ['label' => 'Lost', 'type' => 'int', 'total' => true],
            'rate' => ['label' => 'Win rate', 'type' => 'pct'],
            'followups' => ['label' => 'Follow-ups', 'type' => 'int', 'total' => true],
            'visits' => ['label' => 'Site visits', 'type' => 'int', 'total' => true],
        ];
    }

    public function rows(Filters $f): iterable
    {
        $leads = $f->dates(Lead::query(), 'created_at')->whereNotNull('assigned_to')
            ->when($f->user, fn ($q) => $q->where('assigned_to', $f->user))
            ->get(['id', 'assigned_to', 'status'])->groupBy('assigned_to');
        $followups = $f->dates(LeadFollowup::query(), 'date')->whereNotNull('assigned_to')
            ->when($f->user, fn ($q) => $q->where('assigned_to', $f->user))
            ->selectRaw('assigned_to, COUNT(*) as n')->groupBy('assigned_to')->pluck('n', 'assigned_to');
        $visits = $f->dates(SiteVisit::query(), 'visit_date')->whereNotNull('assigned_to')
            ->when($f->user, fn ($q) => $q->where('assigned_to', $f->user))
            ->selectRaw('assigned_to, COUNT(*) as n')->groupBy('assigned_to')->pluck('n', 'assigned_to');

        $ids = $leads->keys()->merge($followups->keys())->merge($visits->keys())->unique();
        $users = User::whereIn('id', $ids)->orderBy('name')->get(['id', 'name']);

        foreach ($users as $u) {
            /** @var Collection $mine */
            $mine = $leads[$u->id] ?? collect();
            $won = $mine->where('status', LeadStatus::Won)->count();
            $lost = $mine->where('status', LeadStatus::Lost)->count();
            yield [
                'staff' => $u->name,
                'leads' => $mine->count(),
                'open' => $mine->filter(fn ($l) => $l->status->isOpen())->count(),
                'won' => $won,
                'lost' => $lost,
                'rate' => $won + $lost > 0 ? round($won / ($won + $lost) * 100) : null,
                'followups' => (int) ($followups[$u->id] ?? 0),
                'visits' => (int) ($visits[$u->id] ?? 0),
            ];
        }
    }
}
