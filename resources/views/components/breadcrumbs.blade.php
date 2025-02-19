@props([
    'breadcrumbs' => [],
    'separator' => '›',
    'class' => '',
])

<nav {{ $attributes->class(['text-sm font-medium text-gray-600 ' . $class]) }}>
    <ol class="flex items-center space-x-2">
        @foreach ($breadcrumbs as $url => $label)
            <li class="flex items-center">
                @if (! $loop->first)
                    <span class="mx-2">{{ $separator }}</span>
                @endif

                @if (is_int($url))
                    <span>{{ $label }}</span>
                @else
                    <a
                        href="{{ $url }}"
                        class="hover:text-gray-900 transition-colors duration-200"
                    >
                        {{ $label }}
                    </a>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
