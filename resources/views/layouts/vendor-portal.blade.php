<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- Dark-mode toggle (components/theme-toggle.blade.php, below): runs
         synchronously, before any CSS paints, so the page never flashes
         the wrong theme on load. Mirrored verbatim in layouts/admin.blade
         .php and client-portal.blade.php. --}}
    <script>
        (function () {
            try {
                var stored = localStorage.getItem('theme');
                var dark = stored ? stored === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
                document.documentElement.classList.toggle('dark', dark);
            } catch (e) {}
        })();
    </script>
    {{-- Phase 13: Manrope (sans) + Fraunces (serif) design-system fonts. --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&display=swap" rel="stylesheet">
    <title>{{ isset($title) ? $title.' — ' : '' }}Vendor Portal — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full font-sans antialiased">
    {{-- Mirrors layouts/client-portal.blade.php — simple top nav, no
         sidebar, no notification bell (Phase 11 scope: staff-side only). --}}
    <header class="border-b border-gray-200 bg-surface">
        <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-600 text-sm font-bold text-white">
                    {{ substr(config('app.name'), 0, 1) }}
                </div>
                <span class="text-sm font-semibold text-gray-900">Vendor Portal</span>
            </div>
            <x-portal-nav guard="vendor" class="hidden gap-5 text-sm sm:flex" />
            <div class="flex items-center gap-3">
                <span class="hidden text-sm text-gray-500 sm:inline">{{ auth('vendor')->user()->name }}</span>
                <x-theme-toggle />
                <form method="POST" action="{{ route('vendor.logout') }}">
                    @csrf
                    <button type="submit" class="btn-secondary">Log out</button>
                </form>
            </div>
        </div>
        <x-portal-nav guard="vendor" class="flex gap-4 border-t border-gray-100 px-4 py-2 text-xs sm:hidden" />
    </header>

    <main class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8">
        @isset($title)
            <h1 class="mb-6 text-2xl font-semibold text-gray-900">{{ $title }}</h1>
        @endisset

        {{ $slot }}
    </main>

    <x-toast />
    @livewireScripts
</body>
</html>
