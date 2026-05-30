<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PortalProfileController extends Controller
{
    public function show()
    {
        $employee = $this->employee();
        return view('portal.profile.show', compact('employee'));
    }

    public function edit()
    {
        $employee = $this->employee();
        return view('portal.profile.edit', compact('employee'));
    }

    public function update(Request $request)
    {
        $employee = $this->employee();

        $data = $request->validate([
            'phone'                   => 'nullable|string|max:20',
            'emergency_contact_name'  => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'address'                 => 'nullable|string|max:500',
        ]);

        $employee->update($data);

        return redirect()->route('portal.profile.show')
            ->with('success', 'Profile updated successfully.');
    }

    public function updatePhoto(Request $request)
    {
        $employee = $this->employee();

        $request->validate([
            'profile_photo' => 'required|image|max:2048|mimes:jpg,jpeg,png,webp',
        ]);

        if ($employee->profile_photo) {
            \Storage::disk('public')->delete($employee->profile_photo);
        }

        $path = $request->file('profile_photo')->store('employees/photos', 'public');
        $employee->update(['profile_photo' => $path]);

        return back()->with('success', 'Profile photo updated.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        $user = auth()->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password changed successfully.');
    }

    public function changePasswordForm()
    {
        return view('portal.password-change');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        $user = auth()->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $user->update([
            'password'             => Hash::make($request->password),
            'must_change_password' => false,
        ]);

        return redirect()->route('portal.dashboard')
            ->with('success', 'Password updated successfully. Welcome to Imara Logic ERP!');
    }

    private function employee()
    {
        $emp = auth()->user()->employee;
        abort_unless($emp, 403, 'No employee profile linked.');
        return $emp;
    }
}
