<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Imara Logic ERP — Sign In</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center justify-center p-4">

@php
    $hour = (int) now()->format('G');
    $greeting = match(true) {
        $hour >= 5  && $hour < 12 => 'Good Morning',
        $hour >= 12 && $hour < 17 => 'Good Afternoon',
        default                   => 'Good Evening',
    };
@endphp

<div class="w-full max-w-4xl bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col md:flex-row">

    {{-- Left panel --}}
    <div class="bg-gradient-to-br from-blue-950 via-blue-800 to-blue-600 md:w-5/12 p-10 flex flex-col justify-between text-white relative overflow-hidden">

        {{-- Decorative circles --}}
        <div class="absolute -top-10 -left-10 w-48 h-48 bg-white/5 rounded-full"></div>
        <div class="absolute bottom-10 -right-10 w-64 h-64 bg-white/5 rounded-full"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-80 h-80 bg-white/3 rounded-full"></div>

        <div class="relative z-10">
            {{-- Logo mark --}}
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold leading-tight">Imara Logic</h1>
                    <p class="text-blue-300 text-xs tracking-widest uppercase">ERP System</p>
                </div>
            </div>
        </div>

        {{-- Centre tagline --}}
        <div class="relative z-10 text-center py-6">
            <p class="text-4xl font-extrabold text-white/90 leading-tight mb-3">HR & Payroll<br>Made Simple</p>
            <p class="text-blue-200 text-sm">Compliant. Efficient. Built for Kenya.</p>
        </div>

        {{-- Bottom badge --}}
        <div class="relative z-10">
            <div class="inline-flex items-center gap-2 bg-white/10 border border-white/20 rounded-full px-4 py-2">
                <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                <span class="text-xs text-white/80">System Online</span>
            </div>
        </div>
    </div>

    {{-- Right panel — Login form --}}
    <div class="md:w-7/12 p-10 flex flex-col justify-center">

        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-800">{{ $greeting }} 👋</h2>
            <p class="text-gray-400 text-sm mt-1">Sign in to continue to your account</p>
        </div>

        {{-- Session status --}}
        @if(session('status'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm mb-5">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm font-medium text-gray-600 mb-1">Work Email</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                        </svg>
                    </span>
                    <input id="email" type="email" name="email" value="{{ old('email') }}"
                           class="block w-full border border-gray-300 rounded-xl pl-10 pr-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                           placeholder="you@imaralogic.co.ke" required autofocus>
                </div>
                @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password --}}
            <div>
                <label for="password" class="block text-sm font-medium text-gray-600 mb-1">Password</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </span>
                    <input id="password" type="password" name="password"
                           class="block w-full border border-gray-300 rounded-xl pl-10 pr-12 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                           placeholder="••••••••" required>
                    <button type="button" onclick="togglePwd()"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none transition"
                            tabindex="-1" aria-label="Show/hide password">
                        <svg id="eyeOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg id="eyeClosed" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 text-sm text-gray-500 cursor-pointer select-none">
                    <input type="checkbox" name="remember" class="rounded border-gray-300 text-blue-600">
                    Keep me signed in
                </label>
                @if(Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm text-blue-600 hover:underline">Forgot password?</a>
                @endif
            </div>

            <button type="submit"
                    class="w-full bg-blue-700 hover:bg-blue-800 active:bg-blue-900 text-white font-semibold py-3 rounded-xl text-sm transition-all duration-150 shadow-md hover:shadow-lg mt-1">
                Sign In →
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-gray-100 text-center">
            <p class="text-xs text-gray-400">
                Need help? Contact
                <a href="mailto:support@imaralogic.co.ke" class="text-blue-600 hover:underline font-medium">support@imaralogic.co.ke</a>
            </p>
        </div>

    </div>
</div>

{{-- Footer --}}
<p class="text-center text-xs text-gray-400 mt-5">
    &copy; {{ date('Y') }} Imara Logic Ltd. All rights reserved.
</p>

<script>
function togglePwd() {
    const input     = document.getElementById('password');
    const eyeOpen   = document.getElementById('eyeOpen');
    const eyeClosed = document.getElementById('eyeClosed');
    const showing   = input.type === 'text';
    input.type = showing ? 'password' : 'text';
    eyeOpen.classList.toggle('hidden', !showing);
    eyeClosed.classList.toggle('hidden', showing);
}
</script>

</body>
</html>
