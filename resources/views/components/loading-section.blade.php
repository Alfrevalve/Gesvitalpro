@props([
    'height' => '8rem',
    'class' => '',
])

<div
    class="animate-pulse bg-gray-100 rounded-lg {{ $class }}"
    style="height: {{ $height }}"
>
    <div class="flex items-center justify-center h-full">
        <x-loading-indicator class="w-8 h-8 text-gray-400" />
    </div>
</div>
