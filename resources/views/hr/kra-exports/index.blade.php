@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">KRA Export Management</h1>
            <p class="text-gray-600">Manage tax compliance exports for PAYE, NSSF, SHIF, and P10 reports</p>
        </div>

        <!-- Alerts -->
        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <div class="text-red-800">
                    <p class="font-semibold">Error</p>
                    <ul class="mt-2 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-600">Total Exports</div>
                <div class="text-2xl font-bold text-gray-900 mt-2">{{ $statistics['total_exports'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-600">P10 Exports</div>
                <div class="text-2xl font-bold text-blue-600 mt-2">{{ $statistics['p10_exports'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-600">NSSF Exports</div>
                <div class="text-2xl font-bold text-green-600 mt-2">{{ $statistics['nssf_exports'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-600">SHIF Exports</div>
                <div class="text-2xl font-bold text-yellow-600 mt-2">{{ $statistics['shif_exports'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-600">Submitted</div>
                <div class="text-2xl font-bold text-purple-600 mt-2">{{ $statistics['submitted'] }}</div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mb-8">
            <a href="{{ route('hr.kra-exports.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Generate New Export
            </a>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Year</label>
                    <select name="year" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        @foreach ($years as $year)
                            <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Export Type</label>
                    <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        <option value="">All Types</option>
                        @foreach ($exportTypes as $value => $label)
                            <option value="{{ $value }}" {{ $selectedType == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        <option value="">All Statuses</option>
                        <option value="generated" {{ $selectedStatus == 'generated' ? 'selected' : '' }}>Generated</option>
                        <option value="submitted" {{ $selectedStatus == 'submitted' ? 'selected' : '' }}>Submitted</option>
                        <option value="approved" {{ $selectedStatus == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ $selectedStatus == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg">
                        Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Exports Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Export Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Records</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Exported</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($exports as $export)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $export->getTypeLabel() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $export->getPeriodLabel() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $export->record_count }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                KES {{ number_format($export->total_amount ?? 0, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                    {{ $export->status == 'generated' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $export->status == 'submitted' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $export->status == 'approved' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $export->status == 'rejected' ? 'bg-red-100 text-red-800' : '' }}">
                                    {{ $export->getStatusLabel() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $export->exported_at?->format('Y-m-d H:i') ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                <a href="{{ route('hr.kra-exports.show', $export) }}" class="text-blue-600 hover:text-blue-900">View</a>
                                <a href="{{ route('hr.kra-exports.download', $export) }}" class="text-green-600 hover:text-green-900">Download</a>
                                @if ($export->status == 'generated')
                                    <form action="{{ route('hr.kra-exports.destroy', $export) }}" method="POST" class="inline" onsubmit="return confirm('Delete this export?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-600">
                                No exports found. <a href="{{ route('hr.kra-exports.create') }}" class="text-blue-600 hover:text-blue-900">Create one</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($exports->hasPages())
            <div class="mt-6">
                {{ $exports->render() }}
            </div>
        @endif
    </div>
</div>
@endsection
