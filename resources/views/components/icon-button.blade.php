@props([
    'icon' => null,
    'color' => 'primary',
    'size' => 'md',
    'disabled' => false,
    'type' => 'button',
    'class' => '',
])

@php
    $colors = [
        'primary' => 'text-blue-500 hover:text-blue-600 focus:ring-blue-500',
        'secondary' => 'text-gray-500 hover:text-gray-600 focus:ring-gray-500',
        'success' => 'text-green-500 hover:text-green-600 focus:ring-green-500',
        'danger' => 'text-red-500 hover:text-red-600 focus:ring-red-500',
        'warning' => 'text-yellow-500 hover:text-yellow-600 focus:ring-yellow-500',
        'info' => 'text-indigo-500 hover:text-indigo-600 focus:ring-indigo-500'
    ];

    $sizes = [
        'sm' => 'p-1.5',
        'md' => 'p-2',
        'lg' => 'p-3'
    ];
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->class([
        'inline-flex items-center justify-center rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2',
        $colors[$color] ?? $colors['primary'],
        $sizes[$size] ?? $sizes['md'],
        'opacity-50 cursor-not-allowed' => $disabled,
        $class
    ]) }}
    {{ $disabled ? 'disabled' : '' }}
>
    <x-icon :name="$icon" class="w-5 h-5" />
</button>
