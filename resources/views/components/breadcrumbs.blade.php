@props(['items' => []])

@if (count($items))
    <nav {{ $attributes->merge(['class' => 'flex']) }} aria-label="Breadcrumb">
        <ol class="flex items-center space-x-2 text-sm text-gray-500">
            <li>
                <a href="{{ route('dashboard') }}" wire:navigate class="hover:text-gray-700">Dashboard</a>
            </li>
            @foreach ($items as $label => $url)
                <li class="flex items-center space-x-2">
                    <svg class="h-4 w-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                    </svg>
                    @if (is_string($label) && $url)
                        <a href="{{ $url }}" wire:navigate class="hover:text-gray-700">{{ $label }}</a>
                    @else
                        <span class="font-medium text-gray-700">{{ is_string($label) ? $label : $url }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
