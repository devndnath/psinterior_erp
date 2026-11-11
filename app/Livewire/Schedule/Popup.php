<?php

namespace App\Livewire\Schedule;

use App\Services\CalendarEvents;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * The top bar's calendar button: a quick look at the schedule — today and
 * the coming days — without leaving the page. A dropdown on desktop, a
 * bottom sheet on phones. Same items as the Calendar page
 * (CalendarEvents). Nothing is queried until the button is first opened,
 * so it costs ordinary page loads nothing.
 */
class Popup extends Component
{
    public bool $loaded = false;

    /** How far ahead to look: 7 or 30 days. */
    public int $days = 7;

    public function load(): void
    {
        $this->loaded = true;
    }

    public function setDays(int $days): void
    {
        $this->days = in_array($days, [7, 30], true) ? $days : 7;
    }

    public function render()
    {
        $groups = collect();
        $today = CarbonImmutable::today();

        if ($this->loaded && ($user = Auth::user())) {
            $groups = app(CalendarEvents::class)
                ->between($user, $today->toDateString(), $today->addDays($this->days - 1)->toDateString())
                ->sortBy(fn ($e) => $e['date'].' '.($e['time'] ?? '99:99').' '.$e['type'])
                ->groupBy('date');
        }

        return view('livewire.schedule.popup', [
            'groups' => $groups,
            'today' => $today,
        ]);
    }
}
