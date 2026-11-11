<?php

namespace App\Livewire\Calendar;

use App\Models\Project;
use App\Services\CalendarEvents;
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

        $events = app(CalendarEvents::class)->between($user, $monthStart->toDateString(), $monthEnd->toDateString(), $this->project ? (int) $this->project : null);

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
}
