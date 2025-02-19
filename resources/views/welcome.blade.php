<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>GesBio - Sistema de Gestión Biomédica</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <style>
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @keyframes scaleIn {
                from {
                    transform: scale(0.95);
                    opacity: 0;
                }
                to {
                    transform: scale(1);
                    opacity: 1;
                }
            }

            body {
                font-family: 'Figtree', sans-serif;
                margin: 0;
                padding: 0;
                background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
                color: #1a202c;
                overflow-x: hidden;
            }
            .container {
                min-height: 100vh;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                text-align: center;
                padding: 2rem;
            }
            .header {
                margin-bottom: 4rem;
                animation: fadeInUp 0.8s ease-out;
            }
            .logo {
                font-size: 3.5rem;
                font-weight: 600;
                color: #2563eb;
                margin-bottom: 0.5rem;
                text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
            }
            .subtitle {
                font-size: 1.5rem;
                color: #4b5563;
                margin-bottom: 2rem;
                opacity: 0.9;
            }
            .auth-links {
                display: flex;
                gap: 1rem;
                margin-bottom: 4rem;
                animation: scaleIn 0.6s ease-out 0.4s both;
            }
            .auth-link {
                padding: 0.875rem 2rem;
                border-radius: 0.5rem;
                text-decoration: none;
                font-weight: 500;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }
            .login-link {
                background-color: #2563eb;
                color: white;
            }
            .login-link:hover {
                background-color: #1d4ed8;
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
            }
            .register-link {
                background-color: white;
                color: #2563eb;
                border: 2px solid #2563eb;
            }
            .register-link:hover {
                background-color: #f8fafc;
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(37, 99, 235, 0.1);
            }
            .lines {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                gap: 2rem;
                max-width: 1200px;
                width: 100%;
            }
            .line-card {
                background-color: white;
                padding: 2rem;
                border-radius: 1rem;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                display: flex;
                flex-direction: column;
                align-items: center;
                text-align: center;
                animation: scaleIn 0.6s cubic-bezier(0.4, 0, 0.2, 1) both;
                animation-delay: calc(var(--animation-order) * 0.1s);
            }
            .line-card:hover {
                transform: translateY(-5px) scale(1.02);
                box-shadow: 0 8px 16px rgba(37, 99, 235, 0.15);
            }
            .line-icon {
                width: 64px;
                height: 64px;
                margin-bottom: 1rem;
                color: #2563eb;
                transition: transform 0.3s ease;
            }
            .line-card:hover .line-icon {
                transform: scale(1.1);
            }
            .line-name {
                font-size: 1.25rem;
                font-weight: 600;
                color: #1a202c;
                margin-bottom: 0.75rem;
            }
            .line-description {
                color: #6b7280;
                font-size: 0.975rem;
                line-height: 1.5;
            }
            @media (max-width: 640px) {
                .logo {
                    font-size: 2.5rem;
                }
                .subtitle {
                    font-size: 1.25rem;
                }
                .auth-links {
                    flex-direction: column;
                }
                .lines {
                    grid-template-columns: 1fr;
                }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <div class="logo">GesBio</div>
                <div class="subtitle">Sistema de Gestión Biomédica</div>

                @if (Route::has('login'))
                    <div class="auth-links">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="auth-link login-link">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="auth-link login-link">Iniciar Sesión</a>

                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="auth-link register-link">Registrarse</a>
                            @endif
                        @endauth
                    </div>
                @endif
            </div>

            <div class="lines">
                <div class="line-card" style="--animation-order: 1">
                    <svg class="line-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <div class="line-name">Línea de Cráneo</div>
                    <div class="line-description">Equipamiento especializado y de alta precisión para procedimientos neuroquirúrgicos craneales</div>
                </div>
                <div class="line-card" style="--animation-order: 2">
                    <svg class="line-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                    <div class="line-name">Línea de Columna</div>
                    <div class="line-description">Soluciones integrales y tecnología avanzada para cirugías de columna vertebral</div>
                </div>
                <div class="line-card" style="--animation-order: 3">
                    <svg class="line-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                    </svg>
                    <div class="line-name">Línea de Neurocirugía</div>
                    <div class="line-description">Instrumentos y equipos de última generación para procedimientos neuroquirúrgicos</div>
                </div>
                <div class="line-card" style="--animation-order: 4">
                    <svg class="line-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <div class="line-name">Línea de Cirugía</div>
                    <div class="line-description">Equipamiento e instrumental de calidad superior para diversos procedimientos quirúrgicos</div>
                </div>
            </div>
        </div>
    </body>
</html>
