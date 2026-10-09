<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $code }} — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="flex h-full flex-col items-center justify-center px-4 font-sans">
    <p class="text-sm font-semibold text-brand-600">Error {{ $code }}</p>
    <h1 class="mt-2 text-2xl font-bold text-gray-900">{{ $heading }}</h1>
    <p class="mt-2 max-w-sm text-center text-sm text-gray-500">{{ $message }}</p>
    <a href="{{ url('/') }}" class="btn-primary mt-6">Go back home</a>
</body>
</html>
