<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">New Leave Type</h2></x-slot>
    <div class="py-8 max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded-lg p-6">
            <form method="POST" action="{{ route('hr.leave-types.store') }}" class="space-y-4">
                @csrf
                <div>
                    <x-input-label for="name" value="Leave Type Name *" />
                    <x-text-input id="name" name="name" class="mt-1 block w-full" value="{{ old('name') }}" required placeholder="e.g. Annual Leave, Sick Leave" />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="days_per_year" value="Days Per Year *" />
                    <x-text-input id="days_per_year" name="days_per_year" type="number" min="1" max="365" class="mt-1 block w-full" value="{{ old('days_per_year', 21) }}" required />
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="requires_approval" name="requires_approval" value="1" checked class="rounded border-gray-300">
                    <x-input-label for="requires_approval" value="Requires Approval" />
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="is_active" name="is_active" value="1" checked class="rounded border-gray-300">
                    <x-input-label for="is_active" value="Active" />
                </div>
                <div class="flex gap-3 pt-2">
                    <x-primary-button>Save</x-primary-button>
                    <a href="{{ route('hr.leave-types.index') }}" class="text-sm text-gray-500 hover:underline self-center">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
