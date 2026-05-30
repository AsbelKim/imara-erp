<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Change Password — Imara Logic ERP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">

<div class="w-full max-w-4xl bg-white rounded-2xl shadow-xl overflow-hidden flex flex-col md:flex-row">

    {{-- Left branding panel --}}
    <div class="bg-gradient-to-br from-blue-800 to-blue-600 md:w-2/5 p-10 flex flex-col justify-between text-white">
        <div>
            <h1 class="text-2xl font-bold tracking-wide">Imara Logic ERP</h1>
            <p class="text-blue-200 text-sm mt-1">Human Resources & Payroll System</p>
        </div>

        <div class="space-y-5 mt-10">
            <div class="bg-white/10 rounded-xl p-5">
                <div class="flex items-center gap-3 mb-2">
                    <div class="bg-white/20 p-2 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <p class="font-semibold">Security First</p>
                </div>
                <p class="text-blue-200 text-xs leading-relaxed">Your account was set up by HR with a temporary password. Please set a personal password to secure your account.</p>
            </div>

            <div class="text-xs text-blue-300 space-y-1">
                <p class="font-semibold text-white mb-2">Password requirements:</p>
                <p>• At least 8 characters</p>
                <p>• Upper and lowercase letters</p>
                <p>• At least one number</p>
            </div>
        </div>

        <p class="text-blue-300 text-xs mt-10">© {{ date('Y') }} Imara Logic. All rights reserved.</p>
    </div>

    {{-- Right form panel --}}
    <div class="md:w-3/5 p-10 flex flex-col justify-center">

        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-1">Set Your Password</h2>
            <p class="text-gray-400 text-sm">You're required to change your temporary password before accessing the system.</p>
        </div>

        @if(session('warning'))
            <div class="bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 rounded-xl text-sm mb-5 flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                {{ session('warning') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm mb-5">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('portal.password.update') }}" class="space-y-5">
            @csrf

            <div>
                <label for="current_password" class="block text-sm font-medium text-gray-600 mb-1">
                    Current (Temporary) Password
                </label>
                <input id="current_password" type="password" name="current_password"
                       class="block w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                       placeholder="Enter the password given to you by HR" required>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-600 mb-1">
                    New Password
                </label>
                <input id="password" type="password" name="password"
                       class="block w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                       placeholder="Choose a strong password" required>
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-600 mb-1">
                    Confirm New Password
                </label>
                <input id="password_confirmation" type="password" name="password_confirmation"
                       class="block w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                       placeholder="Re-enter your new password" required>
            </div>

            <button type="submit"
                    class="w-full bg-blue-700 hover:bg-blue-800 text-white font-semibold py-3 rounded-xl text-sm transition">
                Set Password & Continue →
            </button>
        </form>

        <div class="mt-6 text-center">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-xs text-gray-400 hover:text-gray-600 hover:underline">
                    Sign out and log in as a different account
                </button>
            </form>
        </div>
    </div>

</div>

</body>
</html>
