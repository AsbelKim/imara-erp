<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Edit Employee — {{ $employee->full_name }}</h2>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded-lg p-6">
            <form method="POST" action="{{ route('hr.employees.update', $employee) }}" class="space-y-6">
                @csrf @method('PATCH')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="first_name" value="First Name *" />
                        <x-text-input id="first_name" name="first_name" class="mt-1 block w-full" value="{{ old('first_name', $employee->first_name) }}" required />
                        <x-input-error :messages="$errors->get('first_name')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="last_name" value="Last Name *" />
                        <x-text-input id="last_name" name="last_name" class="mt-1 block w-full" value="{{ old('last_name', $employee->last_name) }}" required />
                    </div>
                    <div>
                        <x-input-label for="email" value="Email *" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" value="{{ old('email', $employee->email) }}" required />
                        <x-input-error :messages="$errors->get('email')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="phone" value="Phone" />
                        <x-text-input id="phone" name="phone" class="mt-1 block w-full" value="{{ old('phone', $employee->phone) }}" />
                    </div>
                    <div>
                        <x-input-label for="job_title" value="Job Title" />
                        <x-text-input id="job_title" name="job_title" class="mt-1 block w-full" value="{{ old('job_title', $employee->job_title) }}" />
                    </div>
                    <div>
                        <x-input-label for="department_id" value="Department *" />
                        <select id="department_id" name="department_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id', $employee->department_id) == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="status" value="Status *" />
                        <select id="status" name="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            <option value="active" {{ old('status', $employee->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $employee->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="terminated" {{ old('status', $employee->status) == 'terminated' ? 'selected' : '' }}>Terminated</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label for="employment_type" value="Employment Type *" />
                        <select id="employment_type" name="employment_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            <option value="full_time" {{ old('employment_type', $employee->employment_type) == 'full_time' ? 'selected' : '' }}>Full Time</option>
                            <option value="part_time" {{ old('employment_type', $employee->employment_type) == 'part_time' ? 'selected' : '' }}>Part Time</option>
                            <option value="contract" {{ old('employment_type', $employee->employment_type) == 'contract' ? 'selected' : '' }}>Contract</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label for="hire_date" value="Hire Date *" />
                        <x-text-input id="hire_date" name="hire_date" type="date" class="mt-1 block w-full" value="{{ old('hire_date', $employee->hire_date?->format('Y-m-d')) }}" required />
                    </div>
                    <div>
                        <x-input-label for="basic_salary" value="Basic Salary (KES) *" />
                        <x-text-input id="basic_salary" name="basic_salary" type="number" step="0.01" min="0" class="mt-1 block w-full" value="{{ old('basic_salary', $employee->basic_salary) }}" required />
                        <x-input-error :messages="$errors->get('basic_salary')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="national_id" value="National ID" />
                        <x-text-input id="national_id" name="national_id" class="mt-1 block w-full" value="{{ old('national_id', $employee->national_id) }}" />
                    </div>
                    <div>
                        <x-input-label for="kra_pin" value="KRA PIN" />
                        <x-text-input id="kra_pin" name="kra_pin" class="mt-1 block w-full" value="{{ old('kra_pin', $employee->kra_pin) }}" />
                    </div>
                    <div>
                        <x-input-label for="nssf_number" value="NSSF Number" />
                        <x-text-input id="nssf_number" name="nssf_number" class="mt-1 block w-full" value="{{ old('nssf_number', $employee->nssf_number) }}" />
                    </div>
                    <div>
                        <x-input-label for="nhif_number" value="NHIF Number" />
                        <x-text-input id="nhif_number" name="nhif_number" class="mt-1 block w-full" value="{{ old('nhif_number', $employee->nhif_number) }}" />
                    </div>
                    <div>
                        <x-input-label for="bank_name" value="Bank Name" />
                        <x-text-input id="bank_name" name="bank_name" class="mt-1 block w-full" value="{{ old('bank_name', $employee->bank_name) }}" />
                    </div>
                    <div>
                        <x-input-label for="bank_account" value="Bank Account No." />
                        <x-text-input id="bank_account" name="bank_account" class="mt-1 block w-full" value="{{ old('bank_account', $employee->bank_account) }}" />
                    </div>
                </div>

                <div>
                    <x-input-label for="address" value="Address" />
                    <textarea id="address" name="address" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('address', $employee->address) }}</textarea>
                </div>

                <div class="flex gap-3">
                    <x-primary-button>Update Employee</x-primary-button>
                    <a href="{{ route('hr.employees.show', $employee) }}" class="text-sm text-gray-500 hover:underline self-center">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
