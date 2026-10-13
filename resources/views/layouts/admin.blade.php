<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-paper" x-data="{ sidebarOpen: false }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- Phase 13: Manrope (sans) + Fraunces (serif) design-system fonts. --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&display=swap" rel="stylesheet">
    <title>{{ isset($title) ? $title.' — ' : '' }}{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full font-sans antialiased">
    <div class="flex h-full">

        {{-- Mobile backdrop --}}
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
             class="fixed inset-0 z-30 bg-ink-900/60 backdrop-blur-[2px] lg:hidden"></div>

        <x-sidebar />

        <div class="flex min-w-0 flex-1 flex-col">
            <x-topbar />

            <main class="flex-1 overflow-y-auto">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    {{--
                        Phase 16: removed the old fallback <h1> that used
                        to render here from $title (the ['title' => ...]
                        passed into #[Layout('layouts.admin', [...])] by
                        42 Livewire components). Every one of those pages
                        now renders its own heading via <x-page-header> —
                        the Phase 13 component built exactly for this —
                        so this block was printing the page title a
                        *second* time, in the old plain gray-900 sans
                        style, directly above the new serif page-header
                        title. $title is kept as the <title> tag's source
                        above; it's just no longer echoed into the body.
                    --}}
                    @isset($breadcrumbs)
                        <x-breadcrumbs :items="$breadcrumbs" class="mb-4" />
                    @endisset

                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    <x-toast />
    @livewireScripts
</body>
</html>
