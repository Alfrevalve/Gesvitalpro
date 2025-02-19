@props([
    'color' => 'azul',
    'tamano' => 'mediano',
    'icono' => null,
    'posicionIcono' => 'izquierda',
    'deshabilitado' => false,
    'tipo' => 'button',
    'href' => null,
])

@php
    $clases = [
        'boton',
        'boton--'.$color,
        'boton--'.$tamano,
        $deshabilitado ? 'boton--deshabilitado' : '',
    ];

    $tag = $href ? 'a' : 'button';
@endphp

<{{ $tag }}
    @if($href) href="{{ $href }}" @endif
    @if($tipo && $tag === 'button') type="{{ $tipo }}" @endif
    @if($deshabilitado) disabled @endif
    {{ $attributes->class($clases) }}
>
    @if($icono && $posicionIcono === 'izquierda')
        <span class="boton__icono boton__icono--izquierda">
            <i class="{{ $icono }}"></i>
        </span>
    @endif

    <span class="boton__texto">
        {{ $slot }}
    </span>

    @if($icono && $posicionIcono === 'derecha')
        <span class="boton__icono boton__icono--derecha">
            <i class="{{ $icono }}"></i>
        </span>
    @endif
</{{ $tag }}>

<style>
.boton {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.375rem;
    font-weight: 500;
    transition: all 0.15s ease;
    cursor: pointer;
}

.boton--deshabilitado {
    opacity: 0.5;
    cursor: not-allowed;
}

.boton--pequeno {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

.boton--mediano {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}

.boton--grande {
    padding: 0.75rem 1.5rem;
    font-size: 1rem;
}

.boton--azul {
    background-color: #3b82f6;
    color: white;
}

.boton--azul:hover:not(.boton--deshabilitado) {
    background-color: #2563eb;
}

.boton__icono {
    display: inline-flex;
    align-items: center;
}

.boton__icono--izquierda {
    margin-right: 0.5rem;
}

.boton__icono--derecha {
    margin-left: 0.5rem;
}
</style>
