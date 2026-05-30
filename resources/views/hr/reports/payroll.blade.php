<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">Payroll Report</h2>
            <a href="{{ request()->fullUrlWithQuery(['export'=>'csv']) }}"
               class="bg-green-600 hover:bg-green-700 text-white text-sm px-4 py-2 rounded-lg transition">
                Export CSV
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-5">

        {{-- Filters --}}
        <div class="bg-white rounded-xl shadow-sm p-5">
            <form method="GET" class="flex flex-wrap gap-4 items-end">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Year</label>
                    <select name="year" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Month</label>
                    <select name="month" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                        <option value="0">All Months</option>
                        @foreach($months as $num => $name)
                            <option value="{{ $num }}" {{ $selectedMonth == $num ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Department</label>
                    <select name="department_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                        <option value="0">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ $selectedDept == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-5 py-2 rounded-lg transition">
                    Apply Filters
                </button>
            </form>
        </div>

        {{-- Summary KPIs --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl shadow-sm p-4">
                <p class="text-xs text-gray-400 uppercase tracking-wide">Headcount</p>
                <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($summary['headcount']) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4">
                <p class="text-xs text-gray-400 uppercase tracking-wide">Total Gross</p>
                <p class="text-2xl font-bold text-gray-800 mt-1">KES {{ number_format($summary['gross']) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4">
                <p class="text-xs text-gray-400 uppercase tracking-wide">Total Deductions</p>
                <p class="text-2xl font-bold text-red-600 mt-1">KES {{ number_format($summary['deductions']) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4">
                <p class="text-xs text-gray-400 uppercase tracking-wide">Total Net Pay</p>
                <p class="text-2xl font-bold text-green-700 mt-1">KES {{ number_format($summary['net']) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- Deduction breakdown --}}
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-4">Deduction Breakdown</h3>
                <div class="space-y-3 text-sm">
                    @foreach([['NSSF (Employee)', $summary['nssf']], ['NHIF', $summary['nhif']], ['PAYE', $summary['paye']], ['Housing Levy', $summary['housing']]] as [$label, $val])
                    <div class="flex justify-between items-center border-b pb-2 last:border-0">
                        <span class="text-gray-500">{{ $label }}</span>
                        <span class="font-semibold text-red-600">KES {{ number_format($val) }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Department breakdown --}}
            <div class="bg-white rounded-xl shadow-sm p-5 lg:col-span-2">
                <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-4">By Department</h3>
                @if($byDept->isEmpty())
                    <p class="text-sm text-gray-400">No data for this period.</p>
                @else
                <table class="w-full text-sm">
                    <thead class="text-xs text-gray-400 uppercase border-b">
                        <tr>
                            <th class="pb-2 text-left">Department</th>
                            <th class="pb-2 text-right">Staff</th>
                            <th class="pb-2 text-right">Gross (KES)</th>
                            <th class="pb-2 text-right">Net (KES)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($byDept as $dept => $data)
                        <tr>
                            <td class="py-2 font-medium text-gray-700">{{ $dept }}</td>
                            <td class="py-2 text-right text-gray-500">{{ $data['count'] }}</td>
                            <td class="py-2 text-right">{{ number_format($data['gross']) }}</td>
                            <td class="py-2 text-right font-semibold text-green-700">{{ number_format($data['net']) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>

        {{-- Detail table --}}
        <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
            <div class="px-5 py-4 border-b">
                <h3 class="font-semibold text-gray-700">Individual Payslips <span class="text-gray-400 text-sm font-normal">({{ $payslips->count() }} records)</span></h3>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Employee</th>
                        <th class="px-4 py-3 text-left">Dept</th>
                        <th class="px-4 py-3 text-left">Period</th>
                        <th class="px-4 py-3 text-right">Gross</th>
                        <th class="px-4 py-3 text-right">NSSF</th>
                        <th class="px-4 py-3 text-right">NHIF</th>
                        <th class="px-4 py-3 text-right">PAYE</th>
                        <th class="px-4 py-3 text-right">H.Levy</th>
                        <th class="px-4 py-3 text-right">Net Pay</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($payslips as $p)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 font-medium text-gray-800">{{ $p->employee->full_name }}</td>
                        <td class="px-4 py-2 text-gray-500 text-xs">{{ $p->employee->department->name }}</td>
                        <td class="px-4 py-2 text-gray-500 text-xs">{{ $p->payrollRun->period_label }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format($p->gross_salary) }}</td>
                        <td class="px-4 py-2 text-right text-red-500">{{ number_format($p->nssf_employee) }}</td>
                        <td class="px-4 py-2 text-right text-red-500">{{ number_format($p->nhif) }}</td>
                        <td class="px-4 py-2 text-right text-red-500">{{ number_format($p->paye) }}</td>
                        <td class="px-4 py-2 text-right text-red-500">{{ number_format($p->housing_levy) }}</td>
                        <td class="px-4 py-2 text-right font-semibold text-green-700">{{ number_format($p->net_salary) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="px-4 py-10 text-center text-gray-400">No payroll data for the selected filters.</td></tr>
                    @endforelse
                </tbody>
                @if($payslips->count())
                <tfoot class="bg-gray-50 font-semibold text-sm border-t-2 border-gray-200">
                    <tr>
                        <td colspan="3" class="px-4 py-3 text-gray-600">Totals</td>
                        <td class="px-4 py-3 text-right">{{ number_format($summary['gross']) }}</td>
                        <td class="px-4 py-3 text-right text-red-600">{{ number_format($summary['nssf']) }}</td>
                        <td class="px-4 py-3 text-right text-red-600">{{ number_format($summary['nhif']) }}</td>
                        <td class="px-4 py-3 text-right text-red-600">{{ number_format($summary['paye']) }}</td>
                        <td class="px-4 py-3 text-right text-red-600">{{ number_format($summary['housing']) }}</td>
                        <td class="px-4 py-3 text-right text-green-700">{{ number_format($summary['net']) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

    </div>
</x-app-layout>
