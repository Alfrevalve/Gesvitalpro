@echo off
SETLOCAL EnableDelayedExpansion

echo [94mIniciando optimizacion del sistema...[0m

:: Verificar PHP y Composer
where php >nul 2>nul
IF %ERRORLEVEL% NEQ 0 (
    echo [91mPHP no esta instalado. Por favor, instale PHP primero.[0m
    exit /b 1
)

where composer >nul 2>nul
IF %ERRORLEVEL% NEQ 0 (
    echo [91mComposer no esta instalado. Por favor, instale Composer primero.[0m
    exit /b 1
)

:: Configurar memoria para PHP
SET PHP_INI=%~dp0..\php.ini
echo memory_limit = 512M > "%PHP_INI%"
echo max_execution_time = 300 >> "%PHP_INI%"
echo upload_max_filesize = 10M >> "%PHP_INI%"
echo post_max_size = 10M >> "%PHP_INI%"

:: Configurar SSL/TLS para Composer
echo [94mConfigurando SSL/TLS para Composer...[0m
composer config -g disable-tls false
composer config -g secure-http true

:: Limpiar caché de Composer
echo [94mLimpiando cache de Composer...[0m
composer clear-cache

:: Optimizar autoloader
echo [94mOptimizando autoloader...[0m
composer dump-autoload --optimize --no-dev

:: Limpiar caché de Laravel
echo [94mLimpiando cache de Laravel...[0m
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

:: Regenerar caché optimizada
echo [94mRegenerando cache optimizada...[0m
php artisan config:cache
php artisan route:cache
php artisan view:cache

:: Optimizar la aplicación
echo [94mOptimizando la aplicacion...[0m
php artisan optimize

:: Limpiar archivos temporales
echo [94mLimpiando archivos temporales...[0m
IF EXIST storage\logs (
    forfiles /P storage\logs /M *.log /D -7 /C "cmd /c del @path" 2>nul
)
IF EXIST storage\framework\cache (
    forfiles /P storage\framework\cache /M * /D -1 /C "cmd /c del @path" 2>nul
)
IF EXIST storage\framework\sessions (
    forfiles /P storage\framework\sessions /M * /D -1 /C "cmd /c del @path" 2>nul
)
IF EXIST storage\framework\views (
    forfiles /P storage\framework\views /M * /D -1 /C "cmd /c del @path" 2>nul
)

:: Verificar estado final
echo [94mVerificando estado del sistema...[0m
php artisan --version
composer --version
php -v

echo [92m¡Optimizacion completada exitosamente![0m

:: Instrucciones finales
echo.
echo [93mRecomendaciones:[0m
echo 1. Asegurese de que las tareas programadas estan configuradas:
echo    - Abra el Programador de tareas de Windows
echo    - Cree una tarea que ejecute: php artisan schedule:run
echo    - Configure la tarea para ejecutarse cada minuto
echo.
echo 2. Verifique la configuracion en su php.ini:
echo    - memory_limit = 256M
echo    - max_execution_time = 120
echo    - upload_max_filesize = 10M
echo    - post_max_size = 10M
echo.
echo 3. Configure el archivo .env con los valores correctos
echo 4. Reinicie el servidor web si es necesario

:: Verificar actualizaciones pendientes
echo.
echo [94mVerificando actualizaciones pendientes...[0m
composer outdated --direct
IF %ERRORLEVEL% EQU 0 (
    echo [92mTodos los paquetes estan actualizados.[0m
) ELSE (
    echo [93m¡Atencion! Hay paquetes que pueden ser actualizados.[0m
)

ENDLOCAL
