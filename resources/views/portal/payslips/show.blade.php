<x-portal-layout title="Payslip — {{ $payslip->payrollRun->period_label }}">
    <div class="max-w-xl space-y-4 pt-2">

        <div class="flex justify-between items-center">
            <a href="{{ route('portal.payslips.index') }}" class="text-sm text-gray-500 hover:underline">← Back to Payslips</a>
            <a href="{{ route('portal.payslips.download', $payslip) }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">Download PDF</a>
        </div>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            {{-- Header --}}
            <div class="bg-blue-700 text-white px-6 py-4">
                <h2 class="text-lg font-bold">Imara Logic ERP — Payslip</h2>
                <p class="text-blue-200 text-sm">{{ $payslip->payrollRun->period_label }}</p>
            </div>

            {{-- Employee info --}}
            <div class="px-6 py-4 bg-gray-50 border-b grid grid-cols-2 gap-2 text-sm">
                <div><span class="text-gray-400">Name:</span> <strong>{{ $employee->full_name }}</strong></div>
                <div><span class="text-gray-400">Employee No:</span> {{ $employee->employee_number }}</div>
                <div><span class="text-gray-400">Department:</span> {{ $employee->department->name }}</div>
                <div><span class="text-gray-400">KRA PIN:</span> {{ $employee->kra_pin ?? '—' }}</div>
                <div><span class="text-gray-400">NSSF No:</span> {{ $employee->nssf_number ?? '—' }}</div>
                <div><span class="text-gray-400">NHIF No:</span> {{ $employee->nhif_number ?? '—' }}</div>
            </div>

            {{-- Earnings --}}
            <div class="px-6 pt-4">
                <p class="text-xs font-semibold text-gray-400 uppercase mb-2">Earnings</p>
                <div class="flex justify-between text-sm py-1.5 border-b">
                    <span class="text-gray-600">Basic Salary</span>
                    <span>KES {{ number_format($payslip->basic_salary, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm py-1.5 border-b font-semibold bg-gray-50 px-1 rounded">
                    <span>Gross Pay</span>
                    <span>KES {{ number_format($payslip->gross_salary, 2) }}</span>
                </div>
            </div>

            {{-- Deductions --}}
            <div class="px-6 pt-4">
                <p class="text-xs font-semibold text-gray-400 uppercase mb-2">Deductions</p>
                @foreach([
                    ['NSSF (Employee)',    $payslip->nssf_employee],
                    ['NHIF',              $payslip->nhif],
                    ['PAYE',              $payslip->paye],
                    ['Housing Levy (1.5%)', $payslip->housing_levy],
                ] as [$label, $amount])
                <div class="flex justify-between text-sm py-1.5 border-b">
                    <span class="text-gray-600">{{ $label }}</span>
                    <span class="text-red-500">- KES {{ number_format($amount, 2) }}</span>
                </div>
                @endforeach
                <div class="flex justify-between text-sm py-1.5 font-semibold bg-red-50 px-1 rounded">
                    <span>Total Deductions</span>
                    <span class="text-red-600">KES {{ number_format($payslip->total_deductions, 2) }}</span>
                </div>
            </div>

            {{-- Net Pay --}}
            <div class="px-6 py-4">
                <div class="bg-green-50 border border-green-200 rounded-xl px-5 py-4 flex justify-between items-center">
                    <span class="font-bold text-gray-700 text-lg">NET PAY</span>
                    <span class="font-bold text-green-600 text-2xl">KES {{ number_format($payslip->net_salary, 2) }}</span>
                </div>
            </div>

            {{-- Employer contribution note --}}
            <div class="px-6 pb-4 text-xs text-gray-400">
                Employer NSSF contribution: KES {{ number_format($payslip->nssf_employer, 2) }} (not deducted from your pay)
            </div>
        </div>

    </div>
</x-portal-layout>
