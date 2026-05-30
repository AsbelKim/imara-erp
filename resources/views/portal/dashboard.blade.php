<x-portal-layout title="My Dashboard">

    <div class="space-y-6 pt-2">

        {{-- Welcome card --}}
        <div class="bg-gradient-to-r from-blue-700 to-blue-500 rounded-xl p-6 text-white flex items-center gap-5">
            @if($employee->profile_photo)
                <img src="{{ Storage::url($employee->profile_photo) }}" class="w-16 h-16 rounded-full object-cover ring-4 ring-white/30">
            @else
                <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center text-2xl font-bold ring-4 ring-white/30">
                    {{ strtoupper(substr($employee->first_name,0,1).substr($employee->last_name,0,1)) }}
                </div>
            @endif
            <div>
                <p class="text-blue-200 text-sm">Welcome back,</p>
                <h2 class="text-2xl font-bold">{{ $employee->full_name }}</h2>
                <p class="text-blue-200 text-sm mt-0.5">
                    {{ $employee->job_title ?? 'Employee' }} &bull; {{ $employee->department->name }} &bull; Since {{ $employee->hire_date->format('M Y') }}
                </p>
            </div>
        </div>

        {{-- Leave balance cards --}}
        <div>
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Leave Balances ({{ now()->year }})</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
                @foreach($leaveBalances as $balance)
                @php $pct = $balance['total'] > 0 ? round(($balance['used'] / $balance['total']) * 100) : 0; @endphp
                <div class="bg-white rounded-xl shadow-sm p-4">
                    <p class="text-xs text-gray-500 font-medium truncate">{{ $balance['name'] }}</p>
                    <div class="flex items-end justify-between mt-2">
                        <span class="text-2xl font-bold {{ $balance['remaining'] == 0 ? 'text-red-600' : 'text-gray-800' }}">{{ $balance['remaining'] }}</span>
                        <span class="text-xs text-gray-400">/ {{ $balance['total'] }} days</span>
                    </div>
                    <div class="mt-2 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full {{ $pct >= 80 ? 'bg-red-400' : ($pct >= 50 ? 'bg-yellow-400' : 'bg-green-400') }}"
                             style="width: {{ $pct }}%"></div>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">{{ $balance['used'] }} used</p>
                </div>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Latest payslip --}}
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Latest Payslip</h3>
                @if($latestPayslip)
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Period</span>
                        <span class="font-medium">{{ $latestPayslip->payrollRun->period_label }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Gross Salary</span>
                        <span>KES {{ number_format($latestPayslip->gross_salary) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Total Deductions</span>
                        <span class="text-red-500">- KES {{ number_format($latestPayslip->total_deductions) }}</span>
                    </div>
                    <div class="flex justify-between text-sm border-t pt-2 mt-1">
                        <span class="font-semibold text-gray-700">Net Pay</span>
                        <span class="font-bold text-green-600 text-base">KES {{ number_format($latestPayslip->net_salary) }}</span>
                    </div>
                    <div class="pt-2">
                        <a href="{{ route('portal.payslips.show', $latestPayslip) }}" class="text-blue-600 text-xs hover:underline">View details →</a>
                        &nbsp;
                        <a href="{{ route('portal.payslips.download', $latestPayslip) }}" class="text-blue-600 text-xs hover:underline">Download PDF →</a>
                    </div>
                </div>
                @else
                <p class="text-sm text-gray-400">No payslips available yet.</p>
                @endif
            </div>

            {{-- Recent leave requests --}}
            <div class="bg-white rounded-xl shadow-sm p-5">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Recent Leave Requests</h3>
                    <a href="{{ route('portal.leaves.index') }}" class="text-xs text-blue-600 hover:underline">View all</a>
                </div>
                <div class="space-y-2">
                    @forelse($recentLeaves as $leave)
                    <div class="flex items-center justify-between py-2 border-b last:border-0">
                        <div>
                            <p class="text-sm font-medium text-gray-800">{{ $leave->leaveType->name }}</p>
                            <p class="text-xs text-gray-400">{{ $leave->start_date->format('d M') }} – {{ $leave->end_date->format('d M Y') }} · {{ $leave->days_requested }} day(s)</p>
                        </div>
                        @php
                            $badge = ['pending' => 'bg-yellow-100 text-yellow-800', 'approved' => 'bg-green-100 text-green-800', 'rejected' => 'bg-red-100 text-red-700'];
                        @endphp
                        <span class="px-2 py-0.5 rounded-full text-xs {{ $badge[$leave->status] ?? '' }}">{{ ucfirst($leave->status) }}</span>
                    </div>
                    @empty
                    <p class="text-sm text-gray-400">No leave requests yet.</p>
                    @endforelse
                </div>
                <div class="pt-3">
                    <a href="{{ route('portal.leaves.index') }}" class="text-blue-600 text-xs hover:underline">View all leave history →</a>
                </div>
            </div>

        </div>
    </div>

</x-portal-layout>
