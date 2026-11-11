<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    @include('layouts.partials.app-meta')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- Premium-SaaS redesign pass: Inter, replacing Manrope + the Fraunces serif display font (see tailwind.config.js's fontFamily comment for why). --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400..800&display=swap" rel="stylesheet">
    <title>{{ $title ?? config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="app-canvas h-full font-sans antialiased">
    <div class="flex min-h-full flex-col items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
        <div class="w-full max-w-sm">
            <div class="mb-8 flex flex-col items-center">
                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-charcoal text-lg font-extrabold text-accent-500 ring-4 ring-surface dark:bg-accent-500 dark:text-charcoal dark:ring-white/5">
                    {{ substr(config('app.name'), 0, 1) }}
                </div>
                <h1 class="mt-4 text-xl font-semibold text-gray-900">{{ config('app.name') }}</h1>
            </div>

            <div class="card p-7 sm:p-8">
                {{ $slot }}
            </div>
        </div>
    </div>

    <x-toast />
    @livewireScripts
</body>
</html>
