<?php

namespace App\Livewire\Reports;

use App\Enums\AttendanceStatus;
use App\Enums\LeaveApprovalStatus;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Leave;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin', ['title' => 'HR Report'])]
#[Title('HR Report')]
class Hr extends Component
{
    public string $dateFrom = '';
    public string $dateTo = '';

    protected function attendanceByEmployee(): Collection
    {
        $from = $this->dateFrom ?: now()->startOfMonth()->toDateString();
        $to = $this->dateTo ?: now()->toDateString();

        $counts = Attendance::query()
            ->whereBetween('date', [$from, $to])
            ->selectRaw('employee_id, status, count(*) as total')
            ->groupBy('employee_id', 'status')
            ->get()
            ->groupBy('employee_id');

        return Employee::query()->orderBy('name')->get()->map(function (Employee $employee) use ($counts) {
            $rows = $counts->get($employee->id, collect())->pluck('total', 'status');

            return [
                'employee' => $employee,
                'present' => (int) ($rows[AttendanceStatus::Present->value] ?? 0),
                'absent' => (int) ($rows[AttendanceStatus::Absent->value] ?? 0),
                'late' => (int) ($rows[AttendanceStatus::Late->value] ?? 0),
            ];
        });
    }

    protected function pendingLeaveApprovalsCount(): int
    {
        return Leave::where(function ($q) {
            $q->where('manager_status', LeaveApprovalStatus::Pending->value)
                ->orWhere('hr_status', LeaveApprovalStatus::Pending->value);
        })->count();
    }

    public function exportCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $rows = $this->attendanceByEmployee();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Employee', 'Present', 'Absent', 'Late']);

            foreach ($rows as $row) {
                fputcsv($out, [$row['employee']->name, $row['present'], $row['absent'], $row['late']]);
            }

            fclose($out);
        }, 'hr-report.csv');
    }

    public function render()
    {
        return view('livewire.reports.hr', [
            'rows' => $this->attendanceByEmployee(),
            'pendingLeaveApprovals' => $this->pendingLeaveApprovalsCount(),
        ]);
    }
}
