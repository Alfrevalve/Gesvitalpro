<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - GesVitalPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .register-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .logo-container {
            text-align: center;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-container">
            <div class="logo-container">
                @include('components.logo')
            </div>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ url('auth/register') }}">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">Nombre Completo</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required autofocus>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Correo Electrónico</label>
                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>

                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Confirmar Contraseña</label>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                </div>

                <div class="mb-3">
                    <label for="date_of_birth" class="form-label">Fecha de Nacimiento</label>
                    <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}">
                </div>

                <div class="mb-3">
                    <label for="gender" class="form-label">Género</label>
                    <select class="form-select" id="gender" name="gender">
                        <option value="">Seleccionar...</option>
                        <option value="M" {{ old('gender') == 'M' ? 'selected' : '' }}>Masculino</option>
                        <option value="F" {{ old('gender') == 'F' ? 'selected' : '' }}>Femenino</option>
                        <option value="O" {{ old('gender') == 'O' ? 'selected' : '' }}>Otro</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="contact_info" class="form-label">Información de Contacto</label>
                    <input type="text" class="form-control" id="contact_info" name="contact_info" value="{{ old('contact_info') }}" placeholder="Teléfono o dirección">
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Registrarse</button>
                </div>
            </form>

            <div class="mt-3 text-center">
                <p>¿Ya tienes una cuenta? <a href="{{ url('auth/login') }}">Iniciar Sesión</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
