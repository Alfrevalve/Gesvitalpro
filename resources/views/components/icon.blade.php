@props([
    'name' => null,
    'class' => '',
    'src' => null,
])

@if ($src)
    <img
        {{ $attributes->merge(['src' => $src])->class([
            'inline-block',
            $class
        ]) }}
    />
@elseif ($name)
    <svg
        {{ $attributes->class([
            'inline-block fill-current',
            $class
        ]) }}
    >
        <use xlink:href="#icon-{{ $name }}" />
    </svg>
@else
    {{ $slot }}
@endif
