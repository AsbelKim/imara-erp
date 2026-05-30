<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Employee Portal' }} — Imara Logic ERP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans antialiased">

<div class="flex h-screen overflow-hidden">

    {{-- ── Sidebar ── --}}
    <aside class="w-64 bg-blue-900 text-white flex flex-col flex-shrink-0">

        {{-- Logo --}}
        <div class="px-6 py-5 border-b border-blue-800">
            <a href="{{ route('portal.dashboard') }}" class="text-lg font-bold tracking-wide">Imara Logic ERP</a>
            <p class="text-xs text-blue-300 mt-0.5">Employee Portal</p>
        </div>

        {{-- Employee identity card --}}
        @php $emp = auth()->user()->employee; @endphp
        @if($emp)
        <div class="px-6 py-4 border-b border-blue-800 flex items-center gap-3">
            @if($emp->profile_photo)
                <img src="{{ Storage::url($emp->profile_photo) }}" class="w-10 h-10 rounded-full object-cover ring-2 ring-blue-400">
            @else
                <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-sm font-bold ring-2 ring-blue-400">
                    {{ strtoupper(substr($emp->first_name,0,1).substr($emp->last_name,0,1)) }}
                </div>
            @endif
            <div class="overflow-hidden">
                <p class="text-sm font-semibold truncate">{{ $emp->full_name }}</p>
                <p class="text-xs text-blue-300 truncate">{{ $emp->job_title ?? $emp->department->name }}</p>
            </div>
        </div>
        @endif

        {{-- Nav links --}}
        <nav class="flex-1 px-3 py-4 space-y-1">
            @php
                $links = [
                    ['route' => 'portal.dashboard',     'label' => 'Dashboard',   'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                    ['route' => 'portal.profile.show',  'label' => 'My Profile',  'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                    ['route' => 'portal.leaves.index',  'label' => 'My Leave',    'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                    ['route' => 'portal.payslips.index','label' => 'My Payslips', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                ];
            @endphp

            @foreach($links as $link)
            @php $active = request()->routeIs($link['route'].'*'); @endphp
            <a href="{{ route($link['route']) }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition {{ $active ? 'bg-blue-700 text-white font-semibold' : 'text-blue-200 hover:bg-blue-800 hover:text-white' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $link['icon'] }}"/>
                </svg>
                {{ $link['label'] }}
            </a>
            @endforeach
        </nav>

        {{-- Bottom: logout --}}
        <div class="px-3 py-4 border-t border-blue-800">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-blue-200 hover:bg-blue-800 hover:text-white w-full transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Log Out
                </button>
            </form>
        </div>
    </aside>

    {{-- ── Main content ── --}}
    <div class="flex-1 flex flex-col overflow-hidden">

        {{-- Top bar --}}
        <header class="bg-white shadow-sm px-6 py-4 flex items-center justify-between">
            <h1 class="text-lg font-semibold text-gray-800">{{ $title ?? 'Employee Portal' }}</h1>
            <span class="text-sm text-gray-400">{{ now()->format('l, d F Y') }}</span>
        </header>

        {{-- Flash messages --}}
        <div class="px-6 pt-4">
            @if(session('success'))
                <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-lg text-sm mb-4">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded-lg text-sm mb-4">{{ session('error') }}</div>
            @endif
        </div>

        {{-- Page content --}}
        <main class="flex-1 overflow-y-auto px-6 pb-8">
            {{ $slot }}
        </main>
    </div>
</div>

</body>
</html>
