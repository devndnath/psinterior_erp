{{--
    Inline Lucide-style SVG icons, rendered server-side so no JS bundling
    of the `lucide` package (installed but never wired up — confirmed dead
    by audit) is required. Maps an icon name — exactly the strings already
    used in the sidebar's $menu array (see sidebar.blade.php) — to real
    Lucide icon path data.

    <x-icon name="layout-dashboard" class="h-5 w-5" />

    The shapes live in App\Support\Icon::PATHS — add new names there.
--}}
@props(['name'])

<svg {{ $attributes->merge(['class' => 'h-5 w-5', 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '2', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round']) }}>{!! \App\Support\Icon::paths($name) !!}</svg>
