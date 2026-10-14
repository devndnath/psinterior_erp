<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- Premium-SaaS redesign pass: Inter, replacing Manrope + the Fraunces serif display font (see tailwind.config.js's fontFamily comment for why). --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <title>{{ $title ?? config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full font-sans antialiased">
    <div class="flex min-h-full flex-col items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
        <div class="w-full max-w-sm">
            <div class="mb-8 flex flex-col items-center">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-600 text-xl font-bold text-white">
                    {{ substr(config('app.name'), 0, 1) }}
                </div>
                <h1 class="mt-4 text-xl font-semibold text-gray-900">{{ config('app.name') }}</h1>
            </div>

            <div class="card p-6">
                {{ $slot }}
            </div>
        </div>
    </div>

    <x-toast />
    @livewireScripts
</body>
</html>
