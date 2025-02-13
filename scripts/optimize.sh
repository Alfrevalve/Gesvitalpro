#!/bin/bash

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${YELLOW}Iniciando optimización del sistema...${NC}"

# Verificar PHP y Composer
if ! command -v php &> /dev/null; then
    echo -e "${RED}PHP no está instalado. Por favor, instale PHP primero.${NC}"
    exit 1
fi

if ! command -v composer &> /dev/null; then
    echo -e "${RED}Composer no está instalado. Por favor, instale Composer primero.${NC}"
    exit 1
fi

# Configurar SSL/TLS para Composer
echo -e "${YELLOW}Configurando SSL/TLS para Composer...${NC}"
composer config -g disable-tls false
composer config -g secure-http true

# Limpiar caché de Composer
echo -e "${YELLOW}Limpiando caché de Composer...${NC}"
composer clear-cache

# Optimizar autoloader
echo -e "${YELLOW}Optimizando autoloader...${NC}"
composer dump-autoload --optimize --no-dev

# Limpiar caché de Laravel
echo -e "${YELLOW}Limpiando caché de Laravel...${NC}"
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Regenerar caché optimizada
echo -e "${YELLOW}Regenerando caché optimizada...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimizar la aplicación
echo -e "${YELLOW}Optimizando la aplicación...${NC}"
php artisan optimize

# Verificar permisos de almacenamiento
echo -e "${YELLOW}Verificando permisos de almacenamiento...${NC}"
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Limpiar archivos temporales
echo -e "${YELLOW}Limpiando archivos temporales...${NC}"
find storage/logs -name "*.log" -type f -mtime +7 -delete
find storage/framework/cache -type f -mtime +1 -delete
find storage/framework/sessions -type f -mtime +1 -delete
find storage/framework/views -type f -mtime +1 -delete

# Verificar estado final
echo -e "${YELLOW}Verificando estado del sistema...${NC}"
php artisan --version
composer --version
php -v

# Mostrar uso de memoria
echo -e "${YELLOW}Uso de memoria:${NC}"
free -h

echo -e "${GREEN}¡Optimización completada exitosamente!${NC}"

# Instrucciones finales
echo -e "\n${YELLOW}Recomendaciones:${NC}"
echo "1. Asegúrese de que el cron está configurado correctamente:"
echo "* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1"
echo "2. Verifique la configuración de PHP en php.ini:"
echo "   - memory_limit = 256M"
echo "   - max_execution_time = 120"
echo "   - upload_max_filesize = 10M"
echo "   - post_max_size = 10M"
echo "3. Configure el archivo .env con los valores correctos"
echo "4. Reinicie el servidor web si es necesario"

# Verificar si hay actualizaciones pendientes
if composer outdated --direct | grep -q "[^[:space:]]"; then
    echo -e "\n${YELLOW}¡Atención! Hay paquetes que pueden ser actualizados:${NC}"
    composer outdated --direct
fi
