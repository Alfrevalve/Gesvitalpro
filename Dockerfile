# Usar la imagen base de PHP
FROM php:8.2-fpm

# Establecer el directorio de trabajo
WORKDIR /var/www

# Copiar el archivo de configuración de Composer
COPY composer.json composer.lock ./

# Instalar las dependencias de Composer
RUN apt-get update && apt-get install -y libzip-dev && docker-php-ext-install zip
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-dev --optimize-autoloader

# Copiar el resto de la aplicación
COPY . .

# Exponer el puerto 9000
EXPOSE 9000

# Comando para iniciar PHP-FPM
CMD ["php-fpm"]
