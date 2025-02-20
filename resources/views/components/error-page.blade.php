<div class="error-page {{ $darkMode ? 'dark-mode' : '' }}"
     x-data="{ showDetails: false }"
     @if($animations['enabled'])
     x-init="setTimeout(() => { $el.classList.add('show') }, 100)"
     @endif>

    <div class="container">
        <div class="row min-vh-100 align-items-center justify-content-center">
            <div class="col-md-8 text-center">
                <div class="error-content">
                    <!-- Código de Error -->
                    <div class="error-code {{ $darkMode ? 'text-white' : 'text-dark' }}"
                         @if($animations['enabled'])
                         x-init="$el.classList.add('animate-in')"
                         @endif>
                        {{ $code }}
                    </div>

                    <!-- Mensaje de Error -->
                    <h1 class="error-title mb-4 {{ $darkMode ? 'text-white' : 'text-dark' }}">
                        {{ $message }}
                    </h1>

                    <!-- Descripción -->
                    <p class="error-description mb-4 {{ $darkMode ? 'text-white-50' : 'text-muted' }}">
                        {{ $description }}
                    </p>

                    <!-- Ilustración -->
                    <div class="error-illustration mb-4">
                        <img src="{{ asset('img/errors/' . $illustration) }}"
                             alt="Error {{ $code }}"
                             class="img-fluid"
                             style="max-height: 300px;">
                    </div>

                    <!-- Botones de Acción -->
                    <div class="error-actions">
                        <a href="{{ url('/') }}"
                           class="btn btn-primary btn-lg me-3">
                            <i class="fas fa-home me-2"></i>
                            Ir al Inicio
                        </a>

                        <button type="button"
                                class="btn {{ $darkMode ? 'btn-light' : 'btn-dark' }} btn-lg"
                                @click="showDetails = !showDetails">
                            <i class="fas fa-info-circle me-2"></i>
                            Más Detalles
                        </button>
                    </div>

                    <!-- Detalles Técnicos (Colapsable) -->
                    <div x-show="showDetails"
                         x-cloak
                         @if($animations['enabled'])
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 transform translate-y-4"
                         x-transition:enter-end="opacity-100 transform translate-y-0"
                         @endif
                         class="error-details mt-4">
                        <div class="card {{ $darkMode ? 'bg-dark text-white border-secondary' : 'bg-light' }}">
                            <div class="card-body">
                                <h5 class="card-title">Detalles Técnicos</h5>
                                <div class="table-responsive">
                                    <table class="table {{ $darkMode ? 'table-dark' : 'table-light' }} mb-0">
                                        <tbody>
                                            <tr>
                                                <td>Código de Error:</td>
                                                <td><code>{{ $code }}</code></td>
                                            </tr>
                                            <tr>
                                                <td>Timestamp:</td>
                                                <td>{{ now()->format('Y-m-d H:i:s') }}</td>
                                            </tr>
                                            <tr>
                                                <td>URL:</td>
                                                <td><code>{{ request()->url() }}</code></td>
                                            </tr>
                                            @if(app()->environment('local'))
                                            <tr>
                                                <td>User Agent:</td>
                                                <td><small>{{ request()->userAgent() }}</small></td>
                                            </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .error-page {
        min-height: 100vh;
        padding: 2rem 0;
        opacity: 0;
        transition: opacity 0.5s ease;
    }

    .error-page.show {
        opacity: 1;
    }

    .error-page.dark-mode {
        background-color: #1a1a1a;
    }

    .error-code {
        font-size: 8rem;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 1rem;
        opacity: 0;
        transform: translateY(-20px);
    }

    .error-code.animate-in {
        animation: slideDown 0.5s ease forwards;
    }

    .error-title {
        font-size: 2.5rem;
        font-weight: 600;
    }

    .error-description {
        font-size: 1.2rem;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }

    .error-illustration {
        margin: 3rem 0;
        transform: translateY(20px);
        opacity: 0;
        animation: floatUp 0.5s ease forwards 0.3s;
    }

    .error-actions {
        margin-top: 2rem;
    }

    .error-details {
        max-width: 800px;
        margin: 2rem auto;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes floatUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    [x-cloak] {
        display: none !important;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .error-code {
            font-size: 6rem;
        }

        .error-title {
            font-size: 2rem;
        }

        .error-description {
            font-size: 1rem;
        }

        .error-actions .btn {
            display: block;
            width: 100%;
            margin-bottom: 1rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Detectar preferencia de modo oscuro del sistema
    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        document.querySelector('.error-page').classList.add('dark-mode');
    }
</script>
@endpush
