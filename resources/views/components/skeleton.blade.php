{{--
    Loading skeleton placeholder (premium-SaaS redesign spec: "Loading
    skeletons" under reusable components). A plain pulsing bar — compose
    a few with different sizes via the class attribute to approximate the
    shape of whatever's loading.

    <x-skeleton class="h-4 w-3/4" />          {{-- a line of text --}}
    <x-skeleton class="h-10 w-10 rounded-full" /> {{-- an avatar --}}
--}}
<div {{ $attributes->merge(['class' => 'animate-pulse rounded-md bg-ink-100']) }}></div>
