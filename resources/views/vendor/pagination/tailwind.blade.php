{{--
    Premium-SaaS redesign pass, Phase 5: Laravel's own stock Tailwind
    pagination view (vendor/laravel/framework/.../resources/views/
    tailwind.blade.php) ships hardcoded indigo-600/gray-* classes that
    never matched this app's design system — every `{{ $paginator->
    links() }}` call across every index page (40+ views) rendered an
    off-brand indigo active-page pill no matter what the rest of the
    page looked like.

    Laravel's view finder checks resources/views/vendor/pagination/
    tailwind.blade.php (this file) before falling back to the package's
    own copy, so publishing this one file re-themes every paginator in
    the app at once — no per-view changes needed, same "central file"
    leverage as the color-token rebrand. The structure, methods, and
    accessibility markup below are otherwise unchanged from Laravel's
    original so behavior (query-string preservation, aria labels, mobile
    vs desktop layout) stays identical.
--}}
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between">
        <div class="flex flex-1 justify-between sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="btn-secondary cursor-default opacity-50">{{ __('pagination.previous') }}</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="btn-secondary" wire:navigate rel="prev">{{ __('pagination.previous') }}</a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="btn-secondary" wire:navigate rel="next">{{ __('pagination.next') }}</a>
            @else
                <span class="btn-secondary cursor-default opacity-50">{{ __('pagination.next') }}</span>
            @endif
        </div>

        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-ink-500">
                    {!! __('Showing') !!}
                    @if ($paginator->firstItem())
                        <span class="font-medium text-ink-700">{{ $paginator->firstItem() }}</span>
                        {!! __('to') !!}
                        <span class="font-medium text-ink-700">{{ $paginator->lastItem() }}</span>
                    @else
                        <span class="font-medium text-ink-700">{{ $paginator->count() }}</span>
                    @endif
                    {!! __('of') !!}
                    <span class="font-medium text-ink-700">{{ $paginator->total() }}</span>
                    {!! __('results') !!}
                </p>
            </div>

            <div>
                <span class="isolate inline-flex -space-x-px rounded-lg">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}" class="relative inline-flex items-center rounded-l-lg border border-ink-200 bg-surface px-2 py-2 text-ink-300">
                            <span aria-hidden="true"><x-icon name="chevron-left" class="h-4 w-4" /></span>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" wire:navigate rel="prev" class="relative inline-flex items-center rounded-l-lg border border-ink-200 bg-surface px-2 py-2 text-ink-500 transition hover:bg-ink-50 hover:text-ink-700" aria-label="{{ __('pagination.previous') }}">
                            <span aria-hidden="true"><x-icon name="chevron-left" class="h-4 w-4" /></span>
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <span aria-disabled="true" class="relative inline-flex items-center border border-ink-200 bg-surface px-4 py-2 text-sm font-medium text-ink-400">{{ $element }}</span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page" class="relative z-10 inline-flex items-center border border-accent-500 bg-accent-500 px-4 py-2 text-sm font-semibold text-charcoal">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}" wire:navigate class="relative inline-flex items-center border border-ink-200 bg-surface px-4 py-2 text-sm font-medium text-ink-500 transition hover:bg-ink-50 hover:text-ink-700" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" wire:navigate rel="next" class="relative inline-flex items-center rounded-r-lg border border-ink-200 bg-surface px-2 py-2 text-ink-500 transition hover:bg-ink-50 hover:text-ink-700" aria-label="{{ __('pagination.next') }}">
                            <span aria-hidden="true"><x-icon name="chevron-right" class="h-4 w-4" /></span>
                        </a>
                    @else
                        <span aria-disabled="true" aria-label="{{ __('pagination.next') }}" class="relative inline-flex items-center rounded-r-lg border border-ink-200 bg-surface px-2 py-2 text-ink-300">
                            <span aria-hidden="true"><x-icon name="chevron-right" class="h-4 w-4" /></span>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif
