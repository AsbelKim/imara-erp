@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Department-wise Payroll Analysis</h1>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-600">Departments</div>
                <div class="text-3xl font-bold text-blue-600 mt-2">{{ $summary['total_departments'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-600">Total Employees</div>
                <div class="text-3xl font-bold text-green-600 mt-2">{{ $summary['total_employees'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-600">Average Salary</div>
                <div class="text-2xl font-bold text-purple-600 mt-2">KES {{ number_format($summary['avg_salary'] ?? 0, 0) }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-600">Total Gross</div>
                <div class="text-2xl font-bold text-indigo-600 mt-2">KES {{ number_format($summary['total_gross'] ?? 0, 0) }}</div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Department Breakdown</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Headcount</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Gross Total</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Avg Salary</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Net Total</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Deductions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($departmentData as $dept => $data)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $dept }}</td>
                                <td class="px-6 py-4 text-center text-sm text-gray-600">{{ $data['headcount'] }}</td>
                                <td class="px-6 py-4 text-right text-sm text-gray-600">KES {{ number_format($data['gross_total'], 0) }}</td>
                                <td class="px-6 py-4 text-right text-sm text-gray-600">KES {{ number_format($data['gross_avg'], 0) }}</td>
                                <td class="px-6 py-4 text-right text-sm text-gray-600">KES {{ number_format($data['net_total'], 0) }}</td>
                                <td class="px-6 py-4 text-right text-sm text-gray-600">KES {{ number_format($data['deductions'], 0) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">
            <a href="{{ route('hr.advanced-reports.dashboard') }}" class="text-blue-600 hover:text-blue-900">← Back to Dashboard</a>
        </div>
    </div>
</div>
@endsection
