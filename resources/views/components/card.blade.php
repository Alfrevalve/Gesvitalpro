@props([
    'titulo' => null,
    'contenido' => null,
])

<div class="card">
    @if($titulo)
        <div class="card__titulo">
            <h3>{{ $titulo }}</h3>
        </div>
    @endif

    <div class="card__contenido">
        {{ $contenido }}
    </div>
</div>

<style>
.card {
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    padding: 1rem;
    background-color: white;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.card__titulo {
    margin-bottom: 0.5rem;
    font-size: 1.25rem;
    font-weight: 600;
}

.card__contenido {
    font-size: 1rem;
    color: #374151;
}
</style>
