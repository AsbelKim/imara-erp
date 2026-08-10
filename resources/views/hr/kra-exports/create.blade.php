@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Generate KRA Export</h1>
            <p class="text-gray-600">Select the export type and payroll period to generate compliance files</p>
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

        <!-- Available Payroll Runs -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Available Payroll Periods</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Period</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Year</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employees</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($payrollRuns->take(12) as $run)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ date('F', mktime(0, 0, 0, $run->month, 1)) }} {{ $run->year }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $run->year }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $run->payslips_count ?? 0 }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                        {{ ucfirst($run->status) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Export Selection Form -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- P10 Export -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="bg-blue-600 text-white p-6">
                    <h3 class="text-xl font-bold">P10 Payroll List</h3>
                    <p class="text-blue-100 mt-2">Monthly employee earnings and deductions</p>
                </div>
                <form action="{{ route('hr.kra-exports.generate-p10') }}" method="POST" class="p-6">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Year</label>
                            <select name="year" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                                @foreach ($years as $year)
                                    <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Month</label>
                            <select name="month" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                                @foreach ($months as $num => $name)
                                    <option value="{{ $num }}" {{ $num == $selectedMonth ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">
                            Generate P10
                        </button>
                    </div>
                </form>
            </div>

            <!-- NSSF Export -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="bg-green-600 text-white p-6">
                    <h3 class="text-xl font-bold">NSSF Contributions</h3>
                    <p class="text-green-100 mt-2">Employee and employer NSSF contributions</p>
                </div>
                <form action="{{ route('hr.kra-exports.generate-nssf') }}" method="POST" class="p-6">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Year</label>
                            <select name="year" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                                @foreach ($years as $year)
                                    <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Month</label>
                            <select name="month" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                                @foreach ($months as $num => $name)
                                    <option value="{{ $num }}" {{ $num == $selectedMonth ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg">
                            Generate NSSF
                        </button>
                    </div>
                </form>
            </div>

            <!-- SHIF Export -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="bg-yellow-600 text-white p-6">
                    <h3 class="text-xl font-bold">SHIF Contributions</h3>
                    <p class="text-yellow-100 mt-2">Social Health Insurance Fund contributions</p>
                </div>
                <form action="{{ route('hr.kra-exports.generate-shif') }}" method="POST" class="p-6">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Year</label>
                            <select name="year" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                                @foreach ($years as $year)
                                    <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Month</label>
                            <select name="month" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                                @foreach ($months as $num => $name)
                                    <option value="{{ $num }}" {{ $num == $selectedMonth ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="w-full bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded-lg">
                            Generate SHIF
                        </button>
                    </div>
                </form>
            </div>

            <!-- PAYE Summary Export -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="bg-purple-600 text-white p-6">
                    <h3 class="text-xl font-bold">PAYE Summary</h3>
                    <p class="text-purple-100 mt-2">Income tax withholdings summary</p>
                </div>
                <form action="{{ route('hr.kra-exports.generate-paye') }}" method="POST" class="p-6">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Year</label>
                            <select name="year" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                                @foreach ($years as $year)
                                    <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Month</label>
                            <select name="month" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                                @foreach ($months as $num => $name)
                                    <option value="{{ $num }}" {{ $num == $selectedMonth ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg">
                            Generate PAYE
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="mt-8">
            <a href="{{ route('hr.kra-exports.index') }}" class="text-gray-600 hover:text-gray-900">Back to KRA Exports</a>
        </div>
    </div>
</div>
@endsection
