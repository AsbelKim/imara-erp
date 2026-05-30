<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Edit Department — {{ $department->name }}</h2></x-slot>
    <div class="py-8 max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded-lg p-6">
            <form method="POST" action="{{ route('hr.departments.update', $department) }}" class="space-y-4">
                @csrf @method('PATCH')
                <div>
                    <x-input-label for="name" value="Department Name *" />
                    <x-text-input id="name" name="name" class="mt-1 block w-full" value="{{ old('name', $department->name) }}" required />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="code" value="Department Code *" />
                    <x-text-input id="code" name="code" class="mt-1 block w-full" value="{{ old('code', $department->code) }}" required />
                    <x-input-error :messages="$errors->get('code')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="description" value="Description" />
                    <textarea id="description" name="description" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('description', $department->description) }}</textarea>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="is_active" name="is_active" value="1" {{ $department->is_active ? 'checked' : '' }} class="rounded border-gray-300">
                    <x-input-label for="is_active" value="Active" />
                </div>
                <div class="flex gap-3 pt-2">
                    <x-primary-button>Update Department</x-primary-button>
                    <a href="{{ route('hr.departments.index') }}" class="text-sm text-gray-500 hover:underline self-center">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
