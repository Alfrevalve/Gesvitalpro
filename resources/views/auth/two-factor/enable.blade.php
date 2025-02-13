<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Habilitar 2FA - GesVitalPro</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-gray-800">Configurar Autenticación de Dos Factores</h2>
                <p class="text-gray-600 mt-2">Siga los pasos para configurar la autenticación de dos factores</p>
            </div>

            <div class="space-y-6">
                <!-- Paso 1: Escanear código QR -->
                <div class="border rounded-lg p-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Paso 1: Escanear Código QR</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Escanee el siguiente código QR con su aplicación de autenticación (Google Authenticator, Authy, etc.):
                    </p>
                    <div class="flex justify-center mb-4">
                        <img src="https://chart.googleapis.com/chart?cht=qr&chs=200x200&chl={{ urlencode($qrCodeUrl) }}" 
                             alt="Código QR para 2FA"
                             class="border p-2 rounded">
                    </div>
                    <p class="text-sm text-gray-500">
                        Si no puede escanear el código QR, puede ingresar manualmente esta clave en su aplicación:
                        <code class="block mt-2 p-2 bg-gray-100 rounded text-center font-mono">
                            {{ session('two_factor_secret') }}
                        </code>
                    </p>
                </div>

                <!-- Paso 2: Códigos de Recuperación -->
                <div class="border rounded-lg p-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Paso 2: Guardar Códigos de Recuperación</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Guarde estos códigos de recuperación en un lugar seguro. Se pueden usar para acceder a su cuenta si pierde acceso a su dispositivo de autenticación:
                    </p>
                    <div class="bg-gray-100 p-4 rounded-md">
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($recoveryCodes as $code)
                                <code class="font-mono text-sm">{{ $code }}</code>
                            @endforeach
                        </div>
                    </div>
                    <p class="text-sm text-red-600 mt-2">
                        ¡Advertencia! Estos códigos solo se mostrarán una vez.
                    </p>
                </div>

                <!-- Paso 3: Verificar Configuración -->
                <div class="border rounded-lg p-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Paso 3: Verificar Configuración</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Ingrese el código generado por su aplicación de autenticación para confirmar la configuración:
                    </p>
                    <form action="{{ route('two-factor.confirm') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label for="code" class="sr-only">Código de Verificación</label>
                            <input type="text" 
                                   name="code" 
                                   id="code"
                                   required
                                   autocomplete="off"
                                   class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                                   placeholder="Ingrese el código de 6 dígitos">
                        </div>

                        @error('code')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror

                        <button type="submit"
                                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Verificar y Activar
                        </button>
                    </form>
                </div>

                <div class="text-center">
                    <a href="{{ route('two-factor.show') }}" 
                       class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                        Cancelar configuración
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
