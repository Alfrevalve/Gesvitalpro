@props([
    'color' => 'primary',
    'icon' => null,
    'iconPosition' => 'left',
    'size' => 'md',
    'class' => '',
])

@php
    $colors = [
        'primary' => 'bg-blue-100 text-blue-800',
        'secondary' => 'bg-gray-100 text-gray-800',
        'success' => 'bg-green-100 text-green-800',
        'danger' => 'bg-red-100 text-red-800',
        'warning' => 'bg-yellow-100 text-yellow-800',
        'info' => 'bg-indigo-100 text-indigo-800'
    ];

    $sizes = [
        'sm' => 'px-2 py-0.5 text-xs',
        'md' => 'px-2.5 py-1 text-sm',
        'lg' => 'px-3 py-1.5 text-base'
    ];
@endphp

<span {{ $attributes->class([
    'inline-flex items-center rounded-full font-medium',
    $colors[$color] ?? $colors['primary'],
    $sizes[$size] ?? $sizes['md'],
    $class
]) }}>
    @if ($icon && $iconPosition === 'left')
        <x-dynamic-component :component="$icon" class="w-4 h-4 mr-1" />
    @endif

    {{ $slot }}

    @if ($icon && $iconPosition === 'right')
        <x-dynamic-component :component="$icon" class="w-4 h-4 ml-1" />
    @endif
</span>
