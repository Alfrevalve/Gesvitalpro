<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba de Optimización</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .error-card {
            transition: transform 0.3s ease;
        }
        .error-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <h1 class="text-center mb-5">Prueba de Páginas de Error y Optimización</h1>

        <div class="row g-4">
            <!-- Tarjetas de Error -->
            @php
                $errorPages = [
                    400 => ['Bad Request', 'bg-warning'],
                    401 => ['Unauthorized', 'bg-danger'],
                    403 => ['Forbidden', 'bg-danger'],
                    404 => ['Not Found', 'bg-info'],
                    500 => ['Server Error', 'bg-danger'],
                    503 => ['Service Unavailable', 'bg-warning']
                ];
            @endphp

            @foreach($errorPages as $code => $details)
                <div class="col-md-4">
                    <div class="card error-card h-100 shadow-sm">
                        <div class="card-header {{ $details[1] }} text-white">
                            <h5 class="card-title mb-0">{{ $code }} - {{ $details[0] }}</h5>
                        </div>
                        <div class="card-body">
                            <img src="{{ asset('img/errors/' . strtolower(str_replace(' ', '-', $details[0])) . '.svg') }}"
                                 class="img-fluid mb-3"
                                 alt="Error {{ $code }}"
                                 style="height: 150px; width: 100%; object-fit: contain;">
                            <p class="card-text">Prueba la página de error {{ $code }}</p>
                            <a href="{{ url('/test-errors/' . $code) }}"
                               class="btn btn-primary"
                               target="_blank">
                                Ver Error
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Scripts no críticos -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
            // Script de prueba para verificar la optimización
            $(document).ready(function() {
                console.log('jQuery cargado - Este mensaje debería aparecer después de que la página esté lista');
            });
        </script>

        <!-- Script crítico -->
        <script critical>
            console.log('Script crítico - Este mensaje debería aparecer inmediatamente');
        </script>
    </div>

    <!-- Scripts de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
