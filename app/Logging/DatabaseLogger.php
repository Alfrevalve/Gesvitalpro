<?php

namespace App\Logging;

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use Illuminate\Support\Facades\DB;

class DatabaseLogger extends AbstractProcessingHandler
{
    protected function write(LogRecord $record): void
    {
        // Extraer información relevante del registro
        $context = !empty($record->context) ? json_encode($record->context) : null;
        $trace = null;
        $file = null;
        $line = null;

        // Si hay una excepción en el contexto, obtener información adicional
        if (isset($record->context['exception'])) {
            $exception = $record->context['exception'];
            $trace = $exception->getTraceAsString();
            $file = $exception->getFile();
            $line = $exception->getLine();
        }

        // Insertar el registro en la base de datos
        try {
            DB::table('system_logs')->insert([
                'level' => $record->level->name,
                'message' => $record->message,
                'context' => $context,
                'file' => $file,
                'line' => $line,
                'trace' => $trace,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Si falla la inserción en la base de datos, escribir en el archivo de respaldo
            $fallbackPath = storage_path('logs/fallback.log');
            $fallbackMessage = sprintf(
                "[%s] %s: %s %s\n",
                date('Y-m-d H:i:s'),
                $record->level->name,
                $record->message,
                $context ?? ''
            );
            file_put_contents($fallbackPath, $fallbackMessage, FILE_APPEND);
        }
    }

    /**
     * Crear una instancia del logger personalizado.
     */
    public function __invoke(array $config)
    {
        $logger = new Logger('database');
        $logger->pushHandler($this);
        return $logger;
    }
}
