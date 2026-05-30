<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">Leave Types</h2>
            <a href="{{ route('hr.leave-types.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">+ New Leave Type</a>
        </div>
    </x-slot>
    <div class="py-8 max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
        @if(session('success'))<div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded">{{ session('error') }}</div>@endif

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Name</th>
                        <th class="px-4 py-3 text-left">Days/Year</th>
                        <th class="px-4 py-3 text-left">Requires Approval</th>
                        <th class="px-4 py-3 text-left">Requests</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($leaveTypes as $lt)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $lt->name }}</td>
                        <td class="px-4 py-3">{{ $lt->days_per_year }}</td>
                        <td class="px-4 py-3">{{ $lt->requires_approval ? 'Yes' : 'No' }}</td>
                        <td class="px-4 py-3">{{ $lt->leave_requests_count }}</td>
                        <td class="px-4 py-3"><span class="px-2 py-1 rounded-full text-xs {{ $lt->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">{{ $lt->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td class="px-4 py-3 flex gap-2">
                            <a href="{{ route('hr.leave-types.edit', $lt) }}" class="text-yellow-600 hover:underline text-xs">Edit</a>
                            <form method="POST" action="{{ route('hr.leave-types.destroy', $lt) }}" onsubmit="return confirm('Delete this leave type?')">
                                @csrf @method('DELETE')
                                <button class="text-red-500 hover:underline text-xs">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">No leave types yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
