<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">{{ $employee->full_name }} ({{ $employee->employee_number }})</h2>
            <div class="flex gap-2">
                <a href="{{ route('hr.employees.edit', $employee) }}" class="bg-yellow-500 text-white px-3 py-2 rounded text-sm hover:bg-yellow-600">Edit</a>
                <a href="{{ route('hr.employees.index') }}" class="bg-gray-600 text-white px-3 py-2 rounded text-sm hover:bg-gray-700">Back</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <div class="bg-white shadow rounded-lg p-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div><span class="text-gray-500">Department:</span> <span class="font-medium">{{ $employee->department->name }}</span></div>
            <div><span class="text-gray-500">Email:</span> {{ $employee->email }}</div>
            <div><span class="text-gray-500">Phone:</span> {{ $employee->phone ?? '—' }}</div>
            <div><span class="text-gray-500">Gender:</span> {{ ucfirst($employee->gender ?? '—') }}</div>
            <div><span class="text-gray-500">DOB:</span> {{ $employee->date_of_birth?->format('d M Y') ?? '—' }}</div>
            <div><span class="text-gray-500">Hire Date:</span> {{ $employee->hire_date->format('d M Y') }}</div>
            <div><span class="text-gray-500">Employment:</span> {{ str_replace('_',' ', ucfirst($employee->employment_type)) }}</div>
            <div><span class="text-gray-500">Status:</span> {{ ucfirst($employee->status) }}</div>
            <div><span class="text-gray-500">Basic Salary:</span> KES {{ number_format($employee->basic_salary) }}</div>
            <div><span class="text-gray-500">National ID:</span> {{ $employee->national_id ?? '—' }}</div>
            <div><span class="text-gray-500">KRA PIN:</span> {{ $employee->kra_pin ?? '—' }}</div>
            <div><span class="text-gray-500">NSSF No.:</span> {{ $employee->nssf_number ?? '—' }}</div>
            <div><span class="text-gray-500">NHIF No.:</span> {{ $employee->nhif_number ?? '—' }}</div>
            <div><span class="text-gray-500">Bank:</span> {{ $employee->bank_name ?? '—' }} {{ $employee->bank_account ? "/ {$employee->bank_account}" : '' }}</div>
        </div>

        {{-- Leave History --}}
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b"><h3 class="font-semibold text-gray-700">Leave History</h3></div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                    <tr><th class="px-4 py-2 text-left">Type</th><th class="px-4 py-2 text-left">Period</th><th class="px-4 py-2 text-left">Days</th><th class="px-4 py-2 text-left">Status</th></tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($employee->leaveRequests as $leave)
                    <tr>
                        <td class="px-4 py-2">{{ $leave->leaveType->name }}</td>
                        <td class="px-4 py-2">{{ $leave->start_date->format('d M') }} – {{ $leave->end_date->format('d M Y') }}</td>
                        <td class="px-4 py-2">{{ $leave->days_requested }}</td>
                        <td class="px-4 py-2"><span class="px-2 py-0.5 rounded-full text-xs {{ $leave->status == 'approved' ? 'bg-green-100 text-green-800' : ($leave->status == 'rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-800') }}">{{ ucfirst($leave->status) }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-4 py-4 text-center text-gray-400">No leave records.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Payslips --}}
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b"><h3 class="font-semibold text-gray-700">Payslips</h3></div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                    <tr><th class="px-4 py-2 text-left">Period</th><th class="px-4 py-2 text-left">Gross</th><th class="px-4 py-2 text-left">Deductions</th><th class="px-4 py-2 text-left">Net</th><th class="px-4 py-2 text-left">PDF</th></tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($employee->payslips as $slip)
                    <tr>
                        <td class="px-4 py-2">{{ $slip->payrollRun->period_label }}</td>
                        <td class="px-4 py-2">{{ number_format($slip->gross_salary) }}</td>
                        <td class="px-4 py-2">{{ number_format($slip->total_deductions) }}</td>
                        <td class="px-4 py-2 font-semibold">{{ number_format($slip->net_salary) }}</td>
                        <td class="px-4 py-2"><a href="{{ route('hr.payroll.payslip', [$slip->payrollRun, $employee]) }}" class="text-blue-600 hover:underline text-xs">Download</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-4 text-center text-gray-400">No payslips yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</x-app-layout>
