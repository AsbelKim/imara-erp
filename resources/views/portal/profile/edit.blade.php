<x-portal-layout title="Edit Profile">
    <div class="space-y-6 pt-2 max-w-2xl">

        {{-- Profile Photo --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-700 mb-4">Profile Photo</h3>
            <div class="flex items-center gap-5">
                @if($employee->profile_photo)
                    <img src="{{ Storage::url($employee->profile_photo) }}" class="w-16 h-16 rounded-full object-cover">
                @else
                    <div class="w-16 h-16 rounded-full bg-blue-600 flex items-center justify-center text-xl font-bold text-white">
                        {{ strtoupper(substr($employee->first_name,0,1).substr($employee->last_name,0,1)) }}
                    </div>
                @endif
                <form method="POST" action="{{ route('portal.profile.photo') }}" enctype="multipart/form-data" class="flex items-center gap-3">
                    @csrf @method('PATCH')
                    <input type="file" name="profile_photo" accept="image/*" class="text-sm text-gray-600 border rounded px-2 py-1">
                    <button class="bg-blue-600 text-white px-3 py-1.5 rounded text-sm hover:bg-blue-700">Upload</button>
                </form>
            </div>
            @error('profile_photo')<p class="text-red-500 text-xs mt-2">{{ $message }}</p>@enderror
        </div>

        {{-- Editable contact info --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-700 mb-4">Contact Information</h3>
            <form method="POST" action="{{ route('portal.profile.update') }}" class="space-y-4">
                @csrf @method('PATCH')

                <div>
                    <label class="block text-sm text-gray-600 mb-1">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone', $employee->phone) }}" class="block w-full border-gray-300 rounded-lg shadow-sm text-sm px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Emergency Contact Name</label>
                    <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name', $employee->emergency_contact_name) }}" class="block w-full border-gray-300 rounded-lg shadow-sm text-sm px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Emergency Contact Phone</label>
                    <input type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $employee->emergency_contact_phone) }}" class="block w-full border-gray-300 rounded-lg shadow-sm text-sm px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Address</label>
                    <textarea name="address" rows="2" class="block w-full border-gray-300 rounded-lg shadow-sm text-sm px-3 py-2">{{ old('address', $employee->address) }}</textarea>
                </div>

                <div class="flex gap-3">
                    <button class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">Save Changes</button>
                    <a href="{{ route('portal.profile.show') }}" class="text-sm text-gray-500 hover:underline self-center">Cancel</a>
                </div>
            </form>
        </div>

        {{-- Change password --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-700 mb-4">Change Password</h3>
            <form method="POST" action="{{ route('portal.profile.password') }}" class="space-y-4">
                @csrf @method('PATCH')

                <div>
                    <label class="block text-sm text-gray-600 mb-1">Current Password</label>
                    <input type="password" name="current_password" class="block w-full border-gray-300 rounded-lg shadow-sm text-sm px-3 py-2">
                    @error('current_password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">New Password</label>
                    <input type="password" name="password" class="block w-full border-gray-300 rounded-lg shadow-sm text-sm px-3 py-2">
                    @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Confirm New Password</label>
                    <input type="password" name="password_confirmation" class="block w-full border-gray-300 rounded-lg shadow-sm text-sm px-3 py-2">
                </div>

                <button class="bg-gray-800 text-white px-4 py-2 rounded-lg text-sm hover:bg-gray-900">Update Password</button>
            </form>
        </div>

    </div>
</x-portal-layout>
