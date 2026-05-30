<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">Employees</h2>
            <a href="{{ route('hr.employees.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">+ Add Employee</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded">{{ session('success') }}</div>
        @endif

        {{-- Filters --}}
        <form method="GET" class="bg-white shadow rounded-lg p-4 flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, email, number…" class="border rounded px-3 py-2 text-sm w-56">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Department</label>
                <select name="department_id" class="border rounded px-3 py-2 text-sm">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Status</label>
                <select name="status" class="border rounded px-3 py-2 text-sm">
                    <option value="">All</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="terminated" {{ request('status') == 'terminated' ? 'selected' : '' }}>Terminated</option>
                </select>
            </div>
            <button type="submit" class="bg-gray-700 text-white px-4 py-2 rounded text-sm">Filter</button>
            <a href="{{ route('hr.employees.index') }}" class="text-sm text-gray-500 hover:underline">Clear</a>
        </form>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">No.</th>
                        <th class="px-4 py-3 text-left">Name</th>
                        <th class="px-4 py-3 text-left">Department</th>
                        <th class="px-4 py-3 text-left">Email</th>
                        <th class="px-4 py-3 text-left">Hire Date</th>
                        <th class="px-4 py-3 text-left">Salary (KES)</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($employees as $employee)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-gray-500">{{ $employee->employee_number }}</td>
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $employee->full_name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $employee->department->name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $employee->email }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $employee->hire_date->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ number_format($employee->basic_salary) }}</td>
                        <td class="px-4 py-3">
                            @php $colors = ['active'=>'bg-green-100 text-green-800','inactive'=>'bg-gray-100 text-gray-600','terminated'=>'bg-red-100 text-red-700'] @endphp
                            <span class="px-2 py-1 rounded-full text-xs {{ $colors[$employee->status] ?? '' }}">{{ ucfirst($employee->status) }}</span>
                        </td>
                        <td class="px-4 py-3 flex gap-2">
                            <a href="{{ route('hr.employees.show', $employee) }}" class="text-blue-600 hover:underline text-xs">View</a>
                            <a href="{{ route('hr.employees.edit', $employee) }}" class="text-yellow-600 hover:underline text-xs">Edit</a>
                            <form method="POST" action="{{ route('hr.employees.destroy', $employee) }}" onsubmit="return confirm('Soft-delete this employee?')">
                                @csrf @method('DELETE')
                                <button class="text-red-500 hover:underline text-xs">Remove</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-4 py-8 text-center text-gray-400">No employees found.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3">{{ $employees->links() }}</div>
        </div>
    </div>
</x-app-layout>
