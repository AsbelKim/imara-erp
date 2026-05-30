<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Apply for Leave</h2></x-slot>

    <div class="py-8 max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded-lg p-6">
            <form method="POST" action="{{ route('portal.leaves.store') }}" class="space-y-4">
                @csrf

                <div>
                    <x-input-label for="leave_type_id" value="Leave Type *" />
                    <select id="leave_type_id" name="leave_type_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                        <option value="">-- Select --</option>
                        @foreach($leaveTypes as $lt)
                            <option value="{{ $lt->id }}" {{ old('leave_type_id') == $lt->id ? 'selected' : '' }}>{{ $lt->name }} ({{ $lt->days_per_year }} days/yr)</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('leave_type_id')" class="mt-1" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="start_date" value="Start Date *" />
                        <x-text-input id="start_date" name="start_date" type="date" class="mt-1 block w-full" value="{{ old('start_date') }}" required />
                        <x-input-error :messages="$errors->get('start_date')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="end_date" value="End Date *" />
                        <x-text-input id="end_date" name="end_date" type="date" class="mt-1 block w-full" value="{{ old('end_date') }}" required />
                        <x-input-error :messages="$errors->get('end_date')" class="mt-1" />
                    </div>
                </div>

                <div>
                    <x-input-label for="reason" value="Reason (optional)" />
                    <textarea id="reason" name="reason" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('reason') }}</textarea>
                </div>

                <div class="flex gap-3">
                    <x-primary-button>Submit Request</x-primary-button>
                    <a href="{{ route('portal.leaves.index') }}" class="text-sm text-gray-500 hover:underline self-center">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
