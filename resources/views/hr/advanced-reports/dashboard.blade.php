@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Advanced Reports Dashboard</h1>
            <p class="text-gray-600">Strategic analytics, compliance tracking, and payroll insights for {{ $year }}</p>
        </div>

        <!-- Year Selector -->
        <div class="mb-8">
            <form method="GET" class="flex gap-4">
                <select name="year" onchange="this.form.submit()" class="px-4 py-2 border border-gray-300 rounded-lg">
                    @foreach (range(date('Y') - 5, date('Y') + 1) as $y)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <!-- Key Metrics Row -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-600">Total Employees</div>
                <div class="text-3xl font-bold text-blue-600 mt-2">{{ $totalEmployees }}</div>
                <p class="text-xs text-gray-500 mt-2">Active employees</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-600">Departments</div>
                <div class="text-3xl font-bold text-green-600 mt-2">{{ $totalDepartments }}</div>
                <p class="text-xs text-gray-500 mt-2">Active departments</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-600">Gross Payroll</div>
                <div class="text-2xl font-bold text-purple-600 mt-2">KES {{ number_format($totalGrossPay, 0) }}</div>
                <p class="text-xs text-gray-500 mt-2">YTD total</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-600">Net Payroll</div>
                <div class="text-2xl font-bold text-indigo-600 mt-2">KES {{ number_format($totalNetPay, 0) }}</div>
                <p class="text-xs text-gray-500 mt-2">YTD total</p>
            </div>
        </div>

        <!-- Compliance Status -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Compliance Status</h3>
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Payrolls Processed</span>
                        <span class="font-semibold text-gray-900">{{ $complianceStatus['total_payrolls'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Employees Covered</span>
                        <span class="font-semibold text-gray-900">{{ $complianceStatus['employees_covered'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Payslips Generated</span>
                        <span class="font-semibold text-gray-900">{{ $complianceStatus['payslips_generated'] }}</span>
                    </div>
                </div>
            </div>

            <!-- Deductions Breakdown -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Deductions Summary</h3>
                <div class="space-y-2">
                    @foreach ($deductionsBreakdown as $name => $amount)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">{{ $name }}</span>
                            <span class="font-semibold text-gray-900">KES {{ number_format($amount, 0) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Quick Links -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Reports</h3>
                <div class="space-y-2">
                    <a href="{{ route('hr.advanced-reports.employee-turnover') }}" class="block text-sm text-blue-600 hover:text-blue-900">
                        Employee Turnover →
                    </a>
                    <a href="{{ route('hr.advanced-reports.payroll-cost-trends') }}" class="block text-sm text-blue-600 hover:text-blue-900">
                        Payroll Trends →
                    </a>
                    <a href="{{ route('hr.advanced-reports.statutory-liabilities') }}" class="block text-sm text-blue-600 hover:text-blue-900">
                        Statutory Liabilities →
                    </a>
                    <a href="{{ route('hr.advanced-reports.department-payroll') }}" class="block text-sm text-blue-600 hover:text-blue-900">
                        Department Analysis →
                    </a>
                    <a href="{{ route('hr.advanced-reports.compliance-checklist') }}" class="block text-sm text-blue-600 hover:text-blue-900">
                        Compliance Checklist →
                    </a>
                </div>
            </div>
        </div>

        <!-- Monthly Trend -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Monthly Trend ({{ $year }})</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Month</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employees</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Gross Pay</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Net Pay</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Deductions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($monthlyTrend as $data)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $data['month'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $data['count'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">KES {{ number_format($data['gross'], 0) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">KES {{ number_format($data['net'], 0) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $data['count'] > 0 ? number_format(($data['gross'] - $data['net']) / $data['count'], 0) : 0 }} per person
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Department Distribution -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Department Distribution</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employees</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Gross Pay</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">% of Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($departmentDistribution as $dept => $data)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $dept }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $data['employees'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">KES {{ number_format($data['gross'], 0) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $totalGrossPay > 0 ? number_format(($data['gross'] / $totalGrossPay) * 100, 1) : 0 }}%
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
