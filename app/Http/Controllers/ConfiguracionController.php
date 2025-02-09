<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestEmail;
use DateTimeZone;

class ConfiguracionController extends Controller
{
    /**
     * Mostrar la página de configuraciones
     */
    public function index()
    {
        $config = $this->getConfiguraciones();
        $timezones = DateTimeZone::listIdentifiers();
        $roles = \App\Models\Role::all();

        return view('configuraciones.index', compact('config', 'timezones', 'roles'));
    }

    /**
     * Guardar las configuraciones
     */
    private function processLogo(Request $request)
    {
        if ($request->hasFile('empresa_logo')) {
            $logo = $request->file('empresa_logo');
            $logoPath = $logo->store('public/empresa');
            $this->guardarConfiguracion('empresa_logo', str_replace('public/', '', $logoPath));
        }
    }

    private function processFavicon(Request $request)
    {
        if ($request->hasFile('favicon')) {
            $favicon = $request->file('favicon');
            $faviconPath = $favicon->store('public');
            $this->guardarConfiguracion('favicon', str_replace('public/', '', $faviconPath));
        }
    }

    public function guardar(Request $request)
    {
        try {
            // Validar datos básicos
            $request->validate([
                'empresa_nombre' => 'required|string|max:255',
                'empresa_rif' => 'required|string|max:20',
                'empresa_email' => 'required|email',
                'empresa_logo' => 'nullable|image|max:2048',
                'favicon' => 'nullable|file|mimes:ico,png|max:1024',
                'smtp_host' => 'nullable|string|max:255',
                'smtp_port' => 'nullable|integer',
                'smtp_user' => 'nullable|string|max:255',
                'smtp_password' => 'nullable|string|max:255',
                'min_password_length' => 'required|integer|min:6',
                'session_timeout' => 'required|integer|min:1',
            ]);

            // Procesar y guardar el logo
            $this->processLogo($request);

            // Procesar y guardar el favicon
            $this->processFavicon($request);

            // Guardar configuraciones generales
            $configsToSave = [
                // Información de la empresa
                'empresa_nombre', 'empresa_rif', 'empresa_direccion', 'empresa_telefono', 
                'empresa_email',

                // Configuración regional
                'timezone', 'date_format', 'currency', 'language',

                // Personalización
                'theme', 'primary_color',

                // Email
                'smtp_host', 'smtp_port', 'smtp_user', 'smtp_password', 'smtp_encryption',
                'from_email', 'from_name', 'email_signature',

                // Notificaciones
                'notify_new_users', 'notify_low_stock', 'notify_backups', 'notify_errors',
                'channel_email', 'channel_database', 'channel_slack', 'slack_webhook',

                // Seguridad
                'min_password_length', 'require_uppercase', 'require_numbers', 'require_symbols',
                'password_expiration', 'session_timeout', 'max_login_attempts', 'lockout_duration',
                'two_factor_auth',

                // Registro de actividad
                'log_logins', 'log_failed_logins', 'log_actions', 'log_retention_days'
            ];

            foreach ($configsToSave as $config) {
                if ($request->has($config)) {
                    $value = $request->input($config);
                    
                    // Convertir checkboxes a booleanos
                    if (in_array($config, [
                        'notify_new_users', 'notify_low_stock', 'notify_backups', 'notify_errors',
                        'channel_email', 'channel_database', 'channel_slack',
                        'require_uppercase', 'require_numbers', 'require_symbols',
                        'two_factor_auth', 'log_logins', 'log_failed_logins', 'log_actions'
                    ])) {
                        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    }

                    $this->guardarConfiguracion($config, $value);
                }
            }

            // Actualizar configuraciones del sistema
            $this->actualizarConfiguracionesSistema();

            return response()->json([
                'success' => true,
                'message' => __('auth.config_saved')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('auth.config_save_failed', ['error' => $e->getMessage()])
            ], 500);
        }
    }

    /**
     * Probar la configuración de email
     */
    public function testEmail(Request $request)
    {
        try {
            $request->validate([
                'smtp_host' => 'required|string',
                'smtp_port' => 'required|integer',
                'smtp_user' => 'required|string',
                'smtp_password' => 'required|string',
                'from_email' => 'required|email',
                'from_name' => 'required|string'
            ]);

            // Configurar temporalmente los ajustes de correo
            config([
                'mail.mailers.smtp.host' => $request->smtp_host,
                'mail.mailers.smtp.port' => $request->smtp_port,
                'mail.mailers.smtp.username' => $request->smtp_user,
                'mail.mailers.smtp.password' => $request->smtp_password,
                'mail.mailers.smtp.encryption' => $request->smtp_encryption ?? null,
                'mail.from.address' => $request->from_email,
                'mail.from.name' => $request->from_name,
            ]);

            // Enviar email de prueba
            Mail::to($request->from_email)->send(new TestEmail());

            return response()->json([
                'success' => true,
                'message' => __('auth.email_sent')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('auth.config_save_failed', ['error' => $e->getMessage()])
            ], 500);
        }
    }

    /**
     * Obtener todas las configuraciones
     */
    private function getConfiguraciones()
    {
        $configs = \DB::table('configuraciones')->get();
        $result = [];

        foreach ($configs as $config) {
            // Deserializar valores JSON si es necesario
            $value = $config->value;
            if ($this->isJson($value)) {
                $value = json_decode($value, true);
            }
            
            $result[$config->key] = $value;
        }

        return $result;
    }

    /**
     * Guardar una configuración específica
     */
    private function guardarConfiguracion($key, $value)
    {
        // Serializar arrays u objetos a JSON
        if (is_array($value) || is_object($value)) {
            $value = json_encode($value);
        }

        \DB::table('configuraciones')->updateOrInsert(
            ['key' => $key],
            [
                'value' => $value,
                'updated_at' => now()
            ]
        );
    }

    /**
     * Actualizar las configuraciones del sistema
     */
    private function actualizarConfiguracionesSistema()
    {
        $configs = $this->getConfiguraciones();

        // Actualizar configuraciones de correo
        if (isset($configs['smtp_host'])) {
            config([
                'mail.mailers.smtp.host' => $configs['smtp_host'],
                'mail.mailers.smtp.port' => $configs['smtp_port'],
                'mail.mailers.smtp.username' => $configs['smtp_user'],
                'mail.mailers.smtp.password' => $configs['smtp_password'],
                'mail.mailers.smtp.encryption' => $configs['smtp_encryption'] ?? null,
                'mail.from.address' => $configs['from_email'],
                'mail.from.name' => $configs['from_name'],
            ]);
        }

        // Actualizar zona horaria
        if (isset($configs['timezone'])) {
            config(['app.timezone' => $configs['timezone']]);
        }

        // Actualizar configuraciones de sesión
        if (isset($configs['session_timeout'])) {
            config(['session.lifetime' => $configs['session_timeout']]);
        }

        // Actualizar configuraciones de logging
        if (isset($configs['log_retention_days'])) {
            config(['logging.channels.daily.days' => $configs['log_retention_days']]);
        }
    }

    /**
     * Verificar si una cadena es JSON válido
     */
    private function isJson($string)
    {
        if (!is_string($string)) {
            return false;
        }
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}
