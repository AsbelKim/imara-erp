@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Payroll Cost Trends</h1>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-600">Total Gross Pay ({{ $year }})</div>
                <div class="text-2xl font-bold text-blue-600 mt-2">KES {{ number_format($costDistribution['gross_salary'] ?? 0, 0) }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-600">Average Cost/Employee</div>
                <div class="text-2xl font-bold text-green-600 mt-2">KES {{ number_format(array_sum($costPerEmployee) / count($costPerEmployee), 0) }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-600">Total Deductions</div>
                <div class="text-2xl font-bold text-red-600 mt-2">KES {{ number_format($costDistribution['paye_tax'] + $costDistribution['nssf_employee'] + $costDistribution['nhif'] + $costDistribution['housing_levy'], 0) }}</div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Monthly Breakdown</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Month</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Gross</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">PAYE</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">NSSF</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">NHIF</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Housing</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Net</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($monthlyData as $data)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $data['month'] }}</td>
                                <td class="px-6 py-4 text-right text-sm text-gray-600">KES {{ number_format($data['gross'], 0) }}</td>
                                <td class="px-6 py-4 text-right text-sm text-gray-600">KES {{ number_format($data['paye'], 0) }}</td>
                                <td class="px-6 py-4 text-right text-sm text-gray-600">KES {{ number_format($data['nssf'], 0) }}</td>
                                <td class="px-6 py-4 text-right text-sm text-gray-600">KES {{ number_format($data['nhif'], 0) }}</td>
                                <td class="px-6 py-4 text-right text-sm text-gray-600">KES {{ number_format($data['housing'], 0) }}</td>
                                <td class="px-6 py-4 text-right text-sm text-gray-600">KES {{ number_format($data['net'], 0) }}</td>
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
