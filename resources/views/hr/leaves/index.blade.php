<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Leave Requests</h2>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">

        @if(session('success'))<div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded">{{ session('success') }}</div>@endif

        <form method="GET" class="bg-white shadow rounded-lg p-4 flex gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Status</label>
                <select name="status" class="border rounded px-3 py-2 text-sm">
                    <option value="">All</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <button type="submit" class="bg-gray-700 text-white px-4 py-2 rounded text-sm">Filter</button>
        </form>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Employee</th>
                        <th class="px-4 py-3 text-left">Dept</th>
                        <th class="px-4 py-3 text-left">Leave Type</th>
                        <th class="px-4 py-3 text-left">Period</th>
                        <th class="px-4 py-3 text-left">Days</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($leaves as $leave)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $leave->employee->full_name }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $leave->employee->department->name }}</td>
                        <td class="px-4 py-3">{{ $leave->leaveType->name }}</td>
                        <td class="px-4 py-3 text-xs">{{ $leave->start_date->format('d M Y') }} – {{ $leave->end_date->format('d M Y') }}</td>
                        <td class="px-4 py-3">{{ $leave->days_requested }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs {{ $leave->status == 'approved' ? 'bg-green-100 text-green-800' : ($leave->status == 'rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-800') }}">{{ ucfirst($leave->status) }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @if($leave->status === 'pending')
                            <div class="flex gap-2 items-center">
                                <form method="POST" action="{{ route('hr.leaves.approve', $leave) }}">
                                    @csrf @method('PATCH')
                                    <button class="text-xs bg-green-600 text-white px-2 py-1 rounded hover:bg-green-700">Approve</button>
                                </form>
                                <button onclick="document.getElementById('reject-{{ $leave->id }}').classList.toggle('hidden')" class="text-xs bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600">Reject</button>
                            </div>
                            <div id="reject-{{ $leave->id }}" class="hidden mt-2">
                                <form method="POST" action="{{ route('hr.leaves.reject', $leave) }}" class="flex gap-2">
                                    @csrf @method('PATCH')
                                    <input type="text" name="rejection_reason" placeholder="Reason…" required class="border rounded px-2 py-1 text-xs w-48">
                                    <button class="text-xs bg-red-700 text-white px-2 py-1 rounded">Confirm</button>
                                </form>
                            </div>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">No leave requests.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3">{{ $leaves->links() }}</div>
        </div>
    </div>
</x-app-layout>
