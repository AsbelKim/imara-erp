<x-portal-layout title="My Profile">
    <div class="space-y-6 pt-2 max-w-3xl">

        {{-- Profile header --}}
        <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-5">
            @if($employee->profile_photo)
                <img src="{{ Storage::url($employee->profile_photo) }}" class="w-20 h-20 rounded-full object-cover ring-4 ring-blue-100">
            @else
                <div class="w-20 h-20 rounded-full bg-blue-600 flex items-center justify-center text-2xl font-bold text-white ring-4 ring-blue-100">
                    {{ strtoupper(substr($employee->first_name,0,1).substr($employee->last_name,0,1)) }}
                </div>
            @endif
            <div>
                <h2 class="text-xl font-bold text-gray-800">{{ $employee->full_name }}</h2>
                <p class="text-gray-500">{{ $employee->job_title ?? '—' }} &bull; {{ $employee->department->name }}</p>
                <p class="text-gray-400 text-sm">{{ $employee->employee_number }} &bull; Joined {{ $employee->hire_date->format('d M Y') }}</p>
            </div>
            <div class="ml-auto">
                <a href="{{ route('portal.password.change') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">Change Password</a>
            </div>
        </div>

        {{-- Personal details (read-only) --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-700 mb-4">Personal Information</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><p class="text-gray-400">Full Name</p><p class="font-medium">{{ $employee->full_name }}</p></div>
                <div><p class="text-gray-400">Email</p><p class="font-medium">{{ $employee->email }}</p></div>
                <div><p class="text-gray-400">Phone</p><p class="font-medium">{{ $employee->phone ?? '—' }}</p></div>
                <div><p class="text-gray-400">Gender</p><p class="font-medium">{{ ucfirst($employee->gender ?? '—') }}</p></div>
                <div><p class="text-gray-400">Date of Birth</p><p class="font-medium">{{ $employee->date_of_birth?->format('d M Y') ?? '—' }}</p></div>
                <div><p class="text-gray-400">National ID</p><p class="font-medium">{{ $employee->national_id ?? '—' }}</p></div>
                <div><p class="text-gray-400">Emergency Contact</p><p class="font-medium">{{ $employee->emergency_contact_name ?? '—' }}</p></div>
                <div><p class="text-gray-400">Emergency Phone</p><p class="font-medium">{{ $employee->emergency_contact_phone ?? '—' }}</p></div>
                <div class="col-span-2"><p class="text-gray-400">Address</p><p class="font-medium">{{ $employee->address ?? '—' }}</p></div>
            </div>
        </div>

        {{-- Employment details (read-only) --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-700 mb-4">Employment Details <span class="text-xs text-gray-400 font-normal">(Contact HR to update)</span></h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><p class="text-gray-400">Department</p><p class="font-medium">{{ $employee->department->name }}</p></div>
                <div><p class="text-gray-400">Job Title</p><p class="font-medium">{{ $employee->job_title ?? '—' }}</p></div>
                <div><p class="text-gray-400">Employment Type</p><p class="font-medium">{{ str_replace('_',' ', ucfirst($employee->employment_type)) }}</p></div>
                <div><p class="text-gray-400">Status</p><p class="font-medium">{{ ucfirst($employee->status) }}</p></div>
                <div><p class="text-gray-400">KRA PIN</p><p class="font-medium">{{ $employee->kra_pin ?? '—' }}</p></div>
                <div><p class="text-gray-400">NSSF Number</p><p class="font-medium">{{ $employee->nssf_number ?? '—' }}</p></div>
                <div><p class="text-gray-400">NHIF Number</p><p class="font-medium">{{ $employee->nhif_number ?? '—' }}</p></div>
                <div><p class="text-gray-400">Basic Salary</p><p class="font-medium">KES {{ number_format($employee->basic_salary) }}</p></div>
                <div><p class="text-gray-400">Bank</p><p class="font-medium">{{ $employee->bank_name ?? '—' }}</p></div>
                <div><p class="text-gray-400">Bank Account</p><p class="font-medium">{{ $employee->bank_account ?? '—' }}</p></div>
            </div>
        </div>

    </div>
</x-portal-layout>
