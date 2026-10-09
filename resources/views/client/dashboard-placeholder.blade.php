<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Client Portal — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="flex h-full flex-col items-center justify-center px-4 font-sans">
    <h1 class="text-xl font-semibold text-gray-900">Welcome, {{ auth('client')->user()->name }}</h1>
    <p class="mt-2 max-w-sm text-center text-sm text-gray-500">
        The client guard is wired up and working. The full portal (project progress, designs, approvals, quotations, invoices, payments, documents — spec §43) is built in Phase 11, once every module it reads from exists.
    </p>
    <form method="POST" action="{{ route('client.logout') }}" class="mt-6">
        @csrf
        <button type="submit" class="btn-secondary">Log out</button>
    </form>
</body>
</html>
