<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">My Leave Requests</h2>
            <a href="{{ route('portal.leaves.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">+ Apply for Leave</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
        @if(session('success'))<div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded">{{ session('success') }}</div>@endif

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Leave Type</th>
                        <th class="px-4 py-3 text-left">Period</th>
                        <th class="px-4 py-3 text-left">Days</th>
                        <th class="px-4 py-3 text-left">Reason</th>
                        <th class="px-4 py-3 text-left">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($leaves as $leave)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium">{{ $leave->leaveType->name }}</td>
                        <td class="px-4 py-3 text-xs">{{ $leave->start_date->format('d M Y') }} – {{ $leave->end_date->format('d M Y') }}</td>
                        <td class="px-4 py-3">{{ $leave->days_requested }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $leave->reason ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs {{ $leave->status == 'approved' ? 'bg-green-100 text-green-800' : ($leave->status == 'rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-800') }}">
                                {{ ucfirst($leave->status) }}
                            </span>
                            @if($leave->status === 'rejected' && $leave->rejection_reason)
                                <p class="text-xs text-red-500 mt-1">{{ $leave->rejection_reason }}</p>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">No leave requests yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3">{{ $leaves->links() }}</div>
        </div>
    </div>
</x-app-layout>
