{{--
    Installable-app (PWA) tags: the manifest (served by the
    pwa.manifest route with the company's own name), home-screen icons,
    the browser/status-bar colour in each theme, and iOS "standalone"
    mode so it opens full-screen from the home screen like an app.
--}}
{{-- url() not route(): a server still running an old route cache (deployed
     without `php artisan optimize:clear`) must not 500 every page over this. --}}
<link rel="manifest" href="{{ url('manifest.webmanifest') }}">
<meta name="theme-color" content="#EEEDE8" media="(prefers-color-scheme: light)">
<meta name="theme-color" content="#0F100D" media="(prefers-color-scheme: dark)">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="{{ \Illuminate\Support\Str::limit(\App\Models\Setting::get('company.name', config('app.name')), 12, '') }}">
<link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icons/icon-192.png') }}">
<link rel="apple-touch-icon" href="{{ asset('icons/apple-touch-icon.png') }}">
