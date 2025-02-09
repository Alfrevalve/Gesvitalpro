@extends('layouts.app')

@section('title', 'Configuraciones del Sistema')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-cogs me-2"></i>Configuraciones del Sistema
        </h1>
        <button type="button" class="btn btn-primary" onclick="guardarTodo()">
            <i class="fas fa-save me-2"></i>Guardar Cambios
        </button>
    </div>

    <!-- Tabs de Configuración -->
    <div class="card">
        <div class="card-body">
            <ul class="nav nav-tabs" id="configTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="general-tab" data-bs-toggle="tab" href="#general" role="tab">
                        <i class="fas fa-sliders-h me-2"></i>General
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="email-tab" data-bs-toggle="tab" href="#email" role="tab">
                        <i class="fas fa-envelope me-2"></i>Email
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="notificaciones-tab" data-bs-toggle="tab" href="#notificaciones" role="tab">
                        <i class="fas fa-bell me-2"></i>Notificaciones
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="seguridad-tab" data-bs-toggle="tab" href="#seguridad" role="tab">
                        <i class="fas fa-shield-alt me-2"></i>Seguridad
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="backup-tab" data-bs-toggle="tab" href="#backup" role="tab">
                        <i class="fas fa-database me-2"></i>Respaldos
                    </a>
                </li>
            </ul>

            <div class="tab-content mt-4" id="configTabsContent">
                <!-- Configuración General -->
                <div class="tab-pane fade show active" id="general" role="tabpanel">
                    <form id="generalForm" class="row g-4">
                        <!-- Información de la Empresa -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-building me-2"></i>Información de la Empresa
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <div class="form-floating">
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="nombreEmpresa" 
                                                   name="empresa_nombre" 
                                                   value="{{ $config['empresa_nombre'] ?? '' }}"
                                                   required>
                                            <label for="nombreEmpresa">Nombre de la Empresa</label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-floating">
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="rif" 
                                                   name="empresa_rif" 
                                                   value="{{ $config['empresa_rif'] ?? '' }}"
                                                   required>
                                            <label for="rif">RIF</label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-floating">
                                            <textarea class="form-control" 
                                                      id="direccion" 
                                                      name="empresa_direccion" 
                                                      style="height: 100px">{{ $config['empresa_direccion'] ?? '' }}</textarea>
                                            <label for="direccion">Dirección</label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-floating">
                                            <input type="tel" 
                                                   class="form-control" 
                                                   id="telefono" 
                                                   name="empresa_telefono" 
                                                   value="{{ $config['empresa_telefono'] ?? '' }}">
                                            <label for="telefono">Teléfono</label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-floating">
                                            <input type="email" 
                                                   class="form-control" 
                                                   id="email" 
                                                   name="empresa_email" 
                                                   value="{{ $config['empresa_email'] ?? '' }}">
                                            <label for="email">Email</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Configuración Regional -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-globe me-2"></i>Configuración Regional
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <div class="form-floating">
                                            <select class="form-select" 
                                                    id="timezone" 
                                                    name="timezone">
                                                @foreach($timezones as $tz)
                                                    <option value="{{ $tz }}" 
                                                            {{ ($config['timezone'] ?? '') == $tz ? 'selected' : '' }}>
                                                        {{ $tz }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <label for="timezone">Zona Horaria</label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-floating">
                                            <select class="form-select" 
                                                    id="dateFormat" 
                                                    name="date_format">
                                                <option value="d/m/Y" {{ ($config['date_format'] ?? '') == 'd/m/Y' ? 'selected' : '' }}>
                                                    DD/MM/YYYY (31/12/2023)
                                                </option>
                                                <option value="Y-m-d" {{ ($config['date_format'] ?? '') == 'Y-m-d' ? 'selected' : '' }}>
                                                    YYYY-MM-DD (2023-12-31)
                                                </option>
                                                <option value="m/d/Y" {{ ($config['date_format'] ?? '') == 'm/d/Y' ? 'selected' : '' }}>
                                                    MM/DD/YYYY (12/31/2023)
                                                </option>
                                            </select>
                                            <label for="dateFormat">Formato de Fecha</label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-floating">
                                            <select class="form-select" 
                                                    id="currency" 
                                                    name="currency">
                                                <option value="VES" {{ ($config['currency'] ?? '') == 'VES' ? 'selected' : '' }}>
                                                    Bolívar Venezolano (VES)
                                                </option>
                                                <option value="USD" {{ ($config['currency'] ?? '') == 'USD' ? 'selected' : '' }}>
                                                    Dólar Estadounidense (USD)
                                                </option>
                                                <option value="EUR" {{ ($config['currency'] ?? '') == 'EUR' ? 'selected' : '' }}>
                                                    Euro (EUR)
                                                </option>
                                            </select>
                                            <label for="currency">Moneda</label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-floating">
                                            <select class="form-select" 
                                                    id="language" 
                                                    name="language">
                                                <option value="es" {{ ($config['language'] ?? '') == 'es' ? 'selected' : '' }}>
                                                    Español
                                                </option>
                                                <option value="en" {{ ($config['language'] ?? '') == 'en' ? 'selected' : '' }}>
                                                    English
                                                </option>
                                            </select>
                                            <label for="language">Idioma</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Personalización -->
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-paint-brush me-2"></i>Personalización
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Logo de la Empresa</label>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ asset('storage/' . ($config['empresa_logo'] ?? 'default-logo.png')) }}" 
                                                     alt="Logo" 
                                                     class="me-3" 
                                                     style="max-height: 50px;">
                                                <div class="flex-grow-1">
                                                    <input type="file" 
                                                           class="form-control" 
                                                           id="logo" 
                                                           name="empresa_logo" 
                                                           accept="image/*">
                                                    <div class="form-text">
                                                        Formatos: PNG, JPG. Tamaño máximo: 2MB
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Favicon</label>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ asset('storage/' . ($config['favicon'] ?? 'favicon.ico')) }}" 
                                                     alt="Favicon" 
                                                     class="me-3" 
                                                     style="max-height: 32px;">
                                                <div class="flex-grow-1">
                                                    <input type="file" 
                                                           class="form-control" 
                                                           id="favicon" 
                                                           name="favicon" 
                                                           accept="image/x-icon,image/png">
                                                    <div class="form-text">
                                                        Formatos: ICO, PNG. Tamaño: 32x32px
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Tema del Sistema</label>
                                            <select class="form-select" id="theme" name="theme">
                                                <option value="light" {{ ($config['theme'] ?? '') == 'light' ? 'selected' : '' }}>
                                                    Claro
                                                </option>
                                                <option value="dark" {{ ($config['theme'] ?? '') == 'dark' ? 'selected' : '' }}>
                                                    Oscuro
                                                </option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Color Principal</label>
                                            <input type="color" 
                                                   class="form-control form-control-color w-100" 
                                                   id="primaryColor" 
                                                   name="primary_color" 
                                                   value="{{ $config['primary_color'] ?? '#4e73df' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Configuración de Email -->
                <div class="tab-pane fade" id="email" role="tabpanel">
                    <form id="emailForm" class="row g-4">
                        <!-- Configuración SMTP -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-server me-2"></i>Configuración SMTP
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <div class="form-floating">
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="smtpHost" 
                                                   name="smtp_host" 
                                                   value="{{ $config['smtp_host'] ?? '' }}">
                                            <label for="smtpHost">Servidor SMTP</label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-floating">
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="smtpPort" 
                                                   name="smtp_port" 
                                                   value="{{ $config['smtp_port'] ?? '' }}">
                                            <label for="smtpPort">Puerto SMTP</label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-floating">
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="smtpUser" 
                                                   name="smtp_user" 
                                                   value="{{ $config['smtp_user'] ?? '' }}">
                                            <label for="smtpUser">Usuario SMTP</label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-floating">
                                            <input type="password" 
                                                   class="form-control" 
                                                   id="smtpPassword" 
                                                   name="smtp_password" 
                                                   value="{{ $config['smtp_password'] ?? '' }}">
                                            <label for="smtpPassword">Contraseña SMTP</label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-floating">
                                            <select class="form-select" 
                                                    id="smtpEncryption" 
                                                    name="smtp_encryption">
                                                <option value="">Ninguna</option>
                                                <option value="tls" {{ ($config['smtp_encryption'] ?? '') == 'tls' ? 'selected' : '' }}>
                                                    TLS
                                                </option>
                                                <option value="ssl" {{ ($config['smtp_encryption'] ?? '') == 'ssl' ? 'selected' : '' }}>
                                                    SSL
                                                </option>
                                            </select>
                                            <label for="smtpEncryption">Encriptación</label>
                                        </div>
                                    </div>
                                    <button type="button" 
                                            class="btn btn-info" 
                                            onclick="testEmail()">
                                        <i class="fas fa-paper-plane me-2"></i>Probar Configuración
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Plantillas de Email -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-envelope-open-text me-2"></i>Plantillas de Email
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <div class="form-floating">
                                            <input type="email" 
                                                   class="form-control" 
                                                   id="fromEmail" 
                                                   name="from_email" 
                                                   value="{{ $config['from_email'] ?? '' }}">
                                            <label for="fromEmail">Email Remitente</label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-floating">
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="fromName" 
                                                   name="from_name" 
                                                   value="{{ $config['from_name'] ?? '' }}">
                                            <label for="fromName">Nombre Remitente</label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Firma de Email</label>
                                        <textarea class="form-control" 
                                                  id="emailSignature" 
                                                  name="email_signature" 
                                                  rows="4">{{ $config['email_signature'] ?? '' }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Configuración de Notificaciones -->
                <div class="tab-pane fade" id="notificaciones" role="tabpanel">
                    <form id="notificacionesForm" class="row g-4">
                        <!-- Notificaciones del Sistema -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-bell me-2"></i>Notificaciones del Sistema
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="notifyNewUsers" 
                                               name="notify_new_users"
                                               {{ ($config['notify_new_users'] ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="notifyNewUsers">
                                            Nuevos usuarios registrados
                                        </label>
                                    </div>
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="notifyLowStock" 
                                               name="notify_low_stock"
                                               {{ ($config['notify_low_stock'] ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="notifyLowStock">
                                            Stock bajo en inventario
                                        </label>
                                    </div>
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="notifyBackups" 
                                               name="notify_backups"
                                               {{ ($config['notify_backups'] ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="notifyBackups">
                                            Respaldos del sistema
                                        </label>
                                    </div>
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="notifyErrors" 
                                               name="notify_errors"
                                               {{ ($config['notify_errors'] ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="notifyErrors">
                                            Errores del sistema
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Canales de Notificación -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-bullhorn me-2"></i>Canales de Notificación
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="channelEmail" 
                                               name="channel_email"
                                               {{ ($config['channel_email'] ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="channelEmail">
                                            Email
                                        </label>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="channelDatabase" 
                                               name="channel_database"
                                               {{ ($config['channel_database'] ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="channelDatabase">
                                            Base de datos
                                        </label>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="channelSlack" 
                                               name="channel_slack"
                                               {{ ($config['channel_slack'] ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="channelSlack">
                                            Slack
                                        </label>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-floating">
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="slackWebhook" 
                                                   name="slack_webhook" 
                                                   value="{{ $config['slack_webhook'] ?? '' }}">
                                            <label for="slackWebhook">Webhook de Slack</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Configuración de Seguridad -->
                <div class="tab-pane fade" id="seguridad" role="tabpanel">
                    <form id="seguridadForm" class="row g-4">
                        <!-- Políticas de Contraseña -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-lock me-2"></i>Políticas de Contraseña
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <div class="form-floating">
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="minPasswordLength" 
                                                   name="min_password_length" 
                                                   value="{{ $config['min_password_length'] ?? 8 }}"
                                                   min="6">
                                            <label for="minPasswordLength">Longitud Mínima</label>
                                        </div>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="requireUppercase" 
                                               name="require_uppercase"
                                               {{ ($config['require_uppercase'] ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="requireUppercase">
                                            Requerir mayúsculas
                                        </label>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="requireNumbers" 
                                               name="require_numbers"
                                               {{ ($config['require_numbers'] ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="requireNumbers">
                                            Requerir números
                                        </label>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="requireSymbols" 
                                               name="require_symbols"
                                               {{ ($config['require_symbols'] ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="requireSymbols">
                                            Requerir símbolos
                                        </label>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-floating">
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="passwordExpiration" 
                                                   name="password_expiration" 
                                                   value="{{ $config['password_expiration'] ?? 90 }}"
                                                   min="0">
                                            <label for="passwordExpiration">Días para expiración (0 = nunca)</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sesiones y Acceso -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-shield-alt me-2"></i>Sesiones y Acceso
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <div class="form-floating">
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="sessionTimeout" 
                                                   name="session_timeout" 
                                                   value="{{ $config['session_timeout'] ?? 120 }}"
                                                   min="1">
                                            <label for="sessionTimeout">Tiempo de inactividad (minutos)</label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-floating">
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="maxLoginAttempts" 
                                                   name="max_login_attempts" 
                                                   value="{{ $config['max_login_attempts'] ?? 5 }}"
                                                   min="1">
                                            <label for="maxLoginAttempts">Intentos máximos de login</label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-floating">
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="lockoutDuration" 
                                                   name="lockout_duration" 
                                                   value="{{ $config['lockout_duration'] ?? 15 }}"
                                                   min="1">
                                            <label for="lockoutDuration">Duración del bloqueo (minutos)</label>
                                        </div>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="twoFactorAuth" 
                                               name="two_factor_auth"
                                               {{ ($config['two_factor_auth'] ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="twoFactorAuth">
                                            Habilitar autenticación de dos factores
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Registro de Actividad -->
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-history me-2"></i>Registro de Actividad
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       id="logLogins" 
                                                       name="log_logins"
                                                       {{ ($config['log_logins'] ?? false) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="logLogins">
                                                    Registrar inicios de sesión
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       id="logFailedLogins" 
                                                       name="log_failed_logins"
                                                       {{ ($config['log_failed_logins'] ?? false) ? 'checked' : '' }}>
