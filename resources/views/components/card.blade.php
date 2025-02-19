@props([
    'class' => '',
    'padding' => 'p-6',
])

<div class="bg-white rounded-lg shadow {{ $padding }} {{ $class }}">
    {{ $slot }}
</div>
