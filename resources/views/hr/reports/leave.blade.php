<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">Leave Report</h2>
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
                    <label class="block text-xs font-medium text-gray-500 mb-1">Department</label>
                    <select name="department_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                        <option value="0">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ $selectedDept == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Leave Type</label>
                    <select name="leave_type_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                        <option value="0">All Types</option>
                        @foreach($leaveTypes as $lt)
                            <option value="{{ $lt->id }}" {{ $selectedType == $lt->id ? 'selected' : '' }}>{{ $lt->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                    <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                        <option value="">All Statuses</option>
                        <option value="approved" {{ $selectedStatus === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="pending"  {{ $selectedStatus === 'pending'  ? 'selected' : '' }}>Pending</option>
                        <option value="rejected" {{ $selectedStatus === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-5 py-2 rounded-lg transition">
                    Apply Filters
                </button>
            </form>
        </div>

        {{-- Summary KPIs --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            @foreach([['Total Requests', $summary['total'], 'text-gray-800'], ['Approved', $summary['approved'], 'text-green-700'], ['Pending', $summary['pending'], 'text-yellow-600'], ['Rejected', $summary['rejected'], 'text-red-600'], ['Days Approved', $summary['days'], 'text-blue-700']] as [$label, $val, $color])
            <div class="bg-white rounded-xl shadow-sm p-4">
                <p class="text-xs text-gray-400 uppercase tracking-wide">{{ $label }}</p>
                <p class="text-2xl font-bold {{ $color }} mt-1">{{ number_format($val) }}</p>
            </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- By leave type --}}
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-4">Approved Days by Type</h3>
                @if($byType->isEmpty())
                    <p class="text-sm text-gray-400">No approved leave for this period.</p>
                @else
                <div class="space-y-3">
                    @foreach($byType as $type => $data)
                    <div class="flex justify-between items-center border-b pb-2 last:border-0 text-sm">
                        <span class="text-gray-600">{{ $type }}</span>
                        <span class="font-semibold text-gray-800">{{ $data['days'] }} days <span class="text-gray-400 font-normal">({{ $data['count'] }})</span></span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Detail table --}}
            <div class="bg-white rounded-xl shadow-sm overflow-x-auto lg:col-span-2">
                <div class="px-5 py-4 border-b">
                    <h3 class="font-semibold text-gray-700">Leave Records <span class="text-gray-400 text-sm font-normal">({{ $leaves->count() }} records)</span></h3>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-3 text-left">Employee</th>
                            <th class="px-4 py-3 text-left">Type</th>
                            <th class="px-4 py-3 text-left">Period</th>
                            <th class="px-4 py-3 text-right">Days</th>
                            <th class="px-4 py-3 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($leaves as $l)
                        @php $badge = ['pending'=>'bg-yellow-100 text-yellow-800','approved'=>'bg-green-100 text-green-800','rejected'=>'bg-red-100 text-red-700']; @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2">
                                <p class="font-medium text-gray-800">{{ $l->employee->full_name }}</p>
                                <p class="text-xs text-gray-400">{{ $l->employee->department->name }}</p>
                            </td>
                            <td class="px-4 py-2 text-gray-600 text-xs">{{ $l->leaveType->name }}</td>
                            <td class="px-4 py-2 text-gray-500 text-xs">{{ $l->start_date->format('d M') }} – {{ $l->end_date->format('d M Y') }}</td>
                            <td class="px-4 py-2 text-right font-semibold">{{ $l->days_requested }}</td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-0.5 rounded-full text-xs {{ $badge[$l->status] ?? '' }}">{{ ucfirst($l->status) }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-gray-400">No leave records for the selected filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>
