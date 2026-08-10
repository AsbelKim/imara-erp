@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Statutory Liability Tracking</h1>

        <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
            <div class="bg-blue-50 rounded-lg p-6">
                <div class="text-sm font-medium text-blue-600 mb-1">PAYE</div>
                <div class="text-2xl font-bold text-blue-900">KES {{ number_format($annualTotals['paye'] ?? 0, 0) }}</div>
            </div>
            <div class="bg-green-50 rounded-lg p-6">
                <div class="text-sm font-medium text-green-600 mb-1">NSSF Tier I</div>
                <div class="text-2xl font-bold text-green-900">KES {{ number_format($annualTotals['nssf_tier_1'] ?? 0, 0) }}</div>
            </div>
            <div class="bg-yellow-50 rounded-lg p-6">
                <div class="text-sm font-medium text-yellow-600 mb-1">SHIF</div>
                <div class="text-2xl font-bold text-yellow-900">KES {{ number_format($annualTotals['shif'] ?? 0, 0) }}</div>
            </div>
            <div class="bg-purple-50 rounded-lg p-6">
                <div class="text-sm font-medium text-purple-600 mb-1">Housing Levy</div>
                <div class="text-2xl font-bold text-purple-900">KES {{ number_format($annualTotals['housing_levy'] ?? 0, 0) }}</div>
            </div>
            <div class="bg-red-50 rounded-lg p-6">
                <div class="text-sm font-medium text-red-600 mb-1">Total ({{ $year }})</div>
                <div class="text-2xl font-bold text-red-900">
                    KES {{ number_format(array_sum(array_values($annualTotals)), 0) }}
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Monthly Liabilities</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Month</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">PAYE</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">NSSF Tier I</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">SHIF</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Housing Levy</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($liabilityByMonth as $data)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $data['month'] }}</td>
                                <td class="px-6 py-4 text-right text-sm text-gray-600">KES {{ number_format($data['paye'], 0) }}</td>
                                <td class="px-6 py-4 text-right text-sm text-gray-600">KES {{ number_format($data['nssf_tier_1'], 0) }}</td>
                                <td class="px-6 py-4 text-right text-sm text-gray-600">KES {{ number_format($data['shif'], 0) }}</td>
                                <td class="px-6 py-4 text-right text-sm text-gray-600">KES {{ number_format($data['housing_levy'], 0) }}</td>
                                <td class="px-6 py-4 text-right text-sm font-semibold text-gray-900">KES {{ number_format($data['total'], 0) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Compliance Verification</h2>
            <div class="space-y-2">
                @foreach ($complianceChecklist as $check => $status)
                    <div class="flex items-center justify-between py-2 border-b border-gray-200">
                        <span class="text-gray-700">{{ ucfirst(str_replace('_', ' ', $check)) }}</span>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $status ? 'Verified' : 'Pending' }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-6">
            <a href="{{ route('hr.advanced-reports.dashboard') }}" class="text-blue-600 hover:text-blue-900">← Back to Dashboard</a>
        </div>
    </div>
</div>
@endsection
