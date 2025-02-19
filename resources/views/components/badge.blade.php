@props([
    'color' => 'azul',
    'cerrable' => false,
    'onCerrar' => null,
])

<div {{ $attributes->class([
    'badge',
    'badge--'.$color,
    'badge--cerrable' => $cerrable,
]) }}>
    <span class="badge__texto">
        {{ $slot }}
    </span>

    @if($cerrable)
        <button
            type="button"
            class="badge__cerrar"
            @if($onCerrar) onclick="{{ $onCerrar }}" @endif
        >
            &times;
        </button>
    @endif
</div>

<style>
.badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 500;
}

.badge--azul {
    background-color: #dbeafe;
    color: #1e40af;
}

.badge--rojo {
    background-color: #fee2e2;
    color: #991b1b;
}

.badge--verde {
    background-color: #dcfce7;
    color: #166534;
}

.badge__cerrar {
    margin-left: 0.25rem;
    padding: 0 0.25rem;
    background: transparent;
    border: none;
    cursor: pointer;
    font-size: 1rem;
    line-height: 1;
}

.badge__cerrar:hover {
    opacity: 0.7;
}
</style>
