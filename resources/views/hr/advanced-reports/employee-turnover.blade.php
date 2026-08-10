@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Employee Turnover Analysis</h1>
            <p class="text-gray-600">Track hiring, exits, and organizational changes</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-600">Turnover Rate</div>
                <div class="text-3xl font-bold text-red-600 mt-2">{{ number_format($turnoverRate, 1) }}%</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-600">Departmental Transfers</div>
                <div class="text-3xl font-bold text-blue-600 mt-2">{{ $transfers }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-600">Year</div>
                <div class="text-3xl font-bold text-gray-900 mt-2">{{ $year }}</div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Department-wise Turnover</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Active Employees</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Turnover Rate</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($departmentTurnover as $dept)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $dept['name'] }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $dept['employees'] }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ number_format($dept['turnover_rate'], 1) }}%</td>
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
