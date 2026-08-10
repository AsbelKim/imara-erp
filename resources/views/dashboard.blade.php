<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">HR Admin Dashboard</h2>
            <span class="text-sm text-gray-400">{{ now()->format('l, d F Y') }}</span>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- KPI Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">

                <div class="panel-card p-5 flex items-center gap-4">
                    <div class="bg-blue-100 p-3 rounded-xl">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Active Employees</p>
                        <p class="text-3xl font-bold text-gray-800">{{ $totalEmployees }}</p>
                    </div>
                </div>

                <div class="panel-card p-5 flex items-center gap-4">
                    <div class="bg-yellow-100 p-3 rounded-xl">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Pending Leaves</p>
                        <p class="text-3xl font-bold text-gray-800">{{ $pendingLeaves }}</p>
                    </div>
                </div>

                <div class="panel-card p-5 flex items-center gap-4">
                    <div class="bg-green-100 p-3 rounded-xl">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Last Payroll Net</p>
                        <p class="text-3xl font-bold text-gray-800">
                            @if($lastPayroll)
                                <span class="text-xl">KES</span> {{ number_format($lastPayroll->total_net) }}
                            @else
                                <span class="text-gray-400 text-lg">Not run yet</span>
                            @endif
                        </p>
                    </div>
                </div>

            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

                {{-- Pending Leave Requests --}}
                <div class="panel-card overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-200/80 bg-slate-50">
                        <h3 class="font-semibold text-slate-700">Pending Leave Requests</h3>
                        <a href="{{ route('hr.leaves.index', ['status' => 'pending']) }}" class="text-xs text-blue-600 hover:underline">View all →</a>
                    </div>
                    <div class="divide-y">
                        @forelse($recentLeaves as $leave)
                        <div class="px-5 py-3 flex justify-between items-center">
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ $leave->employee->full_name }}</p>
                                <p class="text-xs text-gray-400">{{ $leave->leaveType->name }} · {{ $leave->days_requested }} day(s) · {{ $leave->start_date->format('d M Y') }}</p>
                            </div>
                            <div class="flex gap-2">
                                <form method="POST" action="{{ route('hr.leaves.approve', $leave) }}">
                                    @csrf @method('PATCH')
                                    <button class="text-xs bg-green-600 text-white px-2 py-1 rounded hover:bg-green-700">Approve</button>
                                </form>
                                <a href="{{ route('hr.leaves.index') }}" class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded hover:bg-gray-200">Review</a>
                            </div>
                        </div>
                        @empty
                        <p class="px-5 py-6 text-sm text-gray-400 text-center">No pending leave requests.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Quick Actions --}}
                <div class="panel-card overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-200/80 bg-slate-50">
                        <h3 class="font-semibold text-slate-700">Quick Actions</h3>
                    </div>
                    <div class="p-5 grid grid-cols-2 gap-3">
                        <a href="{{ route('hr.employees.create') }}"
                           class="flex flex-col items-center justify-center gap-2 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-xl p-4 transition text-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                            <span class="text-xs font-semibold">Add Employee</span>
                        </a>

                        <a href="{{ route('hr.payroll.create') }}"
                           class="flex flex-col items-center justify-center gap-2 bg-green-50 hover:bg-green-100 text-green-700 rounded-xl p-4 transition text-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            <span class="text-xs font-semibold">Run Payroll</span>
                        </a>

                        <a href="{{ route('hr.leaves.index') }}"
                           class="flex flex-col items-center justify-center gap-2 bg-yellow-50 hover:bg-yellow-100 text-yellow-700 rounded-xl p-4 transition text-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span class="text-xs font-semibold">Manage Leaves</span>
                        </a>

                        <a href="{{ route('hr.employees.index') }}"
                           class="flex flex-col items-center justify-center gap-2 bg-purple-50 hover:bg-purple-100 text-purple-700 rounded-xl p-4 transition text-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span class="text-xs font-semibold">View Employees</span>
                        </a>

                        <a href="{{ route('hr.departments.index') }}"
                           class="flex flex-col items-center justify-center gap-2 bg-gray-50 hover:bg-gray-100 text-gray-700 rounded-xl p-4 transition text-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span class="text-xs font-semibold">Departments</span>
                        </a>

                        <a href="{{ route('hr.payroll.index') }}"
                           class="flex flex-col items-center justify-center gap-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl p-4 transition text-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span class="text-xs font-semibold">Payroll History</span>
                        </a>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
