<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-transparent">
            @include('layouts.navigation')

            <div class="lg:flex lg:items-start lg:space-x-6 px-4 py-6 sm:px-6 lg:px-8">
                @include('layouts.sidebar')

                <div class="flex-1 min-w-0">
                    <div class="glass-panel p-6">
                        <!-- Page Heading -->
                        @isset($header)
                            <header class="rounded-[1.5rem] bg-white/90 p-5 shadow-sm shadow-slate-200/60 border border-slate-200/80 mb-6">
                                <div class="max-w-7xl mx-auto">
                                    {{ $header }}
                                </div>
                            </header>
                        @endisset

                        <!-- Page Content -->
                        <main class="space-y-6">
                            {{ $slot }}
                        </main>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
