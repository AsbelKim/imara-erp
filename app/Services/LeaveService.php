<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Notifications\LeaveStatusNotification;
use Illuminate\Support\Facades\Notification;

class LeaveService
{
    public function apply(Employee $employee, array $data): LeaveRequest
    {
        $days = $this->countWorkingDays($data['start_date'], $data['end_date']);

        if ($days <= 0) {
            throw new \InvalidArgumentException('The selected dates contain no working days.');
        }

        $balances = $this->balances($employee);
        $balance  = collect($balances)->firstWhere('id', (int) $data['leave_type_id']);

        if ($balance && $days > $balance['remaining']) {
            throw new \InvalidArgumentException(
                "Insufficient balance. You have {$balance['remaining']} day(s) remaining for {$balance['name']}."
            );
        }

        $overlap = LeaveRequest::where('employee_id', $employee->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($q) use ($data) {
                $q->whereBetween('start_date', [$data['start_date'], $data['end_date']])
                  ->orWhereBetween('end_date', [$data['start_date'], $data['end_date']])
                  ->orWhere(function ($q) use ($data) {
                      $q->where('start_date', '<=', $data['start_date'])
                        ->where('end_date', '>=', $data['end_date']);
                  });
            })->exists();

        if ($overlap) {
            throw new \InvalidArgumentException('You already have a leave request overlapping these dates.');
        }

        return LeaveRequest::create([
            'employee_id'    => $employee->id,
            'leave_type_id'  => $data['leave_type_id'],
            'start_date'     => $data['start_date'],
            'end_date'       => $data['end_date'],
            'days_requested' => $days,
            'reason'         => $data['reason'] ?? null,
            'status'         => 'pending',
        ]);
    }

    public function approve(LeaveRequest $request, int $approvedBy): void
    {
        $request->update([
            'status'      => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
        ]);

        $this->notify($request);
    }

    public function reject(LeaveRequest $request, int $approvedBy, string $reason): void
    {
        $request->update([
            'status'           => 'rejected',
            'approved_by'      => $approvedBy,
            'approved_at'      => now(),
            'rejection_reason' => $reason,
        ]);

        $this->notify($request);
    }

    public function balances(Employee $employee, int $year = null): array
    {
        $year       = $year ?? now()->year;
        $leaveTypes = LeaveType::where('is_active', true)->get();
        $balances   = [];

        foreach ($leaveTypes as $lt) {
            $used = LeaveRequest::where('employee_id', $employee->id)
                ->where('leave_type_id', $lt->id)
                ->where('status', 'approved')
                ->whereYear('start_date', $year)
                ->sum('days_requested');

            $balances[] = [
                'id'        => $lt->id,
                'name'      => $lt->name,
                'total'     => $lt->days_per_year,
                'used'      => (int) $used,
                'remaining' => max(0, $lt->days_per_year - $used),
            ];
        }

        return $balances;
    }

    public function countWorkingDays(string $start, string $end): int
    {
        $current = \Carbon\Carbon::parse($start);
        $endDate = \Carbon\Carbon::parse($end);
        $days    = 0;

        while ($current->lte($endDate)) {
            if (! $current->isWeekend()) {
                $days++;
            }
            $current->addDay();
        }

        return $days;
    }

    private function notify(LeaveRequest $request): void
    {
        $user = $request->employee->user;
        if ($user) {
            $user->notify(new LeaveStatusNotification($request));
        }
    }
}
