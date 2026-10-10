<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title.' — ' : '' }}Vendor Portal — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full font-sans antialiased">
    {{-- Mirrors layouts/client-portal.blade.php — simple top nav, no
         sidebar, no notification bell (Phase 11 scope: staff-side only). --}}
    <header class="border-b border-gray-200 bg-white">
        <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-600 text-sm font-bold text-white">
                    {{ substr(config('app.name'), 0, 1) }}
                </div>
                <span class="text-sm font-semibold text-gray-900">Vendor Portal</span>
            </div>
            <nav class="hidden items-center gap-5 text-sm font-medium text-gray-600 sm:flex">
                <a href="{{ route('vendor.dashboard') }}" wire:navigate class="hover:text-brand-600">Dashboard</a>
                <a href="{{ route('vendor.purchase-orders.index') }}" wire:navigate class="hover:text-brand-600">Purchase Orders</a>
                <a href="{{ route('vendor.ledger.index') }}" wire:navigate class="hover:text-brand-600">Ledger</a>
            </nav>
            <div class="flex items-center gap-3">
                <span class="hidden text-sm text-gray-500 sm:inline">{{ auth('vendor')->user()->name }}</span>
                <form method="POST" action="{{ route('vendor.logout') }}">
                    @csrf
                    <button type="submit" class="btn-secondary">Log out</button>
                </form>
            </div>
        </div>
        <nav class="flex gap-4 overflow-x-auto border-t border-gray-100 px-4 py-2 text-xs font-medium text-gray-600 sm:hidden">
            <a href="{{ route('vendor.dashboard') }}" wire:navigate>Dashboard</a>
            <a href="{{ route('vendor.purchase-orders.index') }}" wire:navigate>Purchase Orders</a>
            <a href="{{ route('vendor.ledger.index') }}" wire:navigate>Ledger</a>
        </nav>
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
