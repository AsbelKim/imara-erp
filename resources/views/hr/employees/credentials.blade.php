<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Employee Account Created</h2>
    </x-slot>

    <div class="py-8 max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded-xl overflow-hidden">

            <div class="bg-green-600 px-6 py-5 text-white flex items-center gap-3">
                <svg class="w-7 h-7 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="font-bold text-lg">Account created successfully</p>
                    <p class="text-green-100 text-sm">Share these credentials with the employee privately</p>
                </div>
            </div>

            <div class="p-6 space-y-4">

                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 flex gap-3">
                    <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    <p class="text-sm text-amber-700">
                        This page will not show the password again. Copy and share these credentials with the employee through a secure channel (in person, phone, or encrypted message).
                    </p>
                </div>

                <div class="border border-gray-200 rounded-lg divide-y">
                    <div class="px-5 py-4 flex justify-between items-center">
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide">Employee</p>
                            <p class="font-semibold text-gray-800 mt-0.5">{{ session('new_employee_name') }}</p>
                        </div>
                        <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full font-medium">Active</span>
                    </div>
                    <div class="px-5 py-4">
                        <p class="text-xs text-gray-400 uppercase tracking-wide">Login Email</p>
                        <p class="font-mono font-semibold text-gray-800 mt-0.5 text-lg">{{ session('new_employee_email') }}</p>
                    </div>
                    <div class="px-5 py-4">
                        <p class="text-xs text-gray-400 uppercase tracking-wide">Temporary Password</p>
                        <div class="flex items-center gap-3 mt-0.5">
                            <p class="font-mono font-semibold text-blue-700 text-lg tracking-widest">{{ session('new_employee_password') }}</p>
                            <button onclick="navigator.clipboard.writeText('{{ session('new_employee_password') }}'); this.textContent='Copied!';"
                                    class="text-xs text-gray-500 border border-gray-300 px-2 py-1 rounded hover:bg-gray-50">
                                Copy
                            </button>
                        </div>
                    </div>
                </div>

                <p class="text-xs text-gray-400 text-center">The employee will be forced to change this password on first login.</p>

                <div class="flex gap-3 pt-2">
                    <a href="{{ route('hr.employees.index') }}"
                       class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition">
                        Back to Employees
                    </a>
                    <a href="{{ route('hr.employees.create') }}"
                       class="border border-gray-300 text-gray-600 hover:bg-gray-50 text-sm font-semibold px-5 py-2.5 rounded-lg transition">
                        Add Another Employee
                    </a>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
