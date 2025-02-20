# GesBio - Sistema de Gestión Quirúrgica

## 📋 Introducción

GesBio es un sistema de gestión quirúrgica desarrollado con Laravel que optimiza y automatiza los procesos relacionados con la gestión de cirugías, equipamiento médico y personal sanitario. El sistema permite:

- Gestión integral de cirugías y procedimientos médicos
- Control y seguimiento de equipamiento médico
- Administración de personal y roles
- Generación de reportes y métricas
- Sistema de notificaciones en tiempo real
- Optimización de procesos quirúrgicos

## 🛠️ Tecnologías Utilizadas

- **PHP 8.1+**
- **Laravel 10.x** - Framework principal
- **MySQL 8.0+** - Base de datos
- **TailwindCSS** - Framework CSS
- **Alpine.js** - Framework JavaScript
- **Laravel Breeze** - Autenticación
- **Laravel Sanctum** - API Authentication
- **AdminLTE** - Panel de administración

## 📋 Requisitos Previos

- PHP >= 8.1
- Composer
- Node.js >= 16.x
- MySQL >= 8.0
- Extensiones PHP requeridas:
  - BCMath
  - Ctype
  - JSON
  - Mbstring
  - OpenSSL
  - PDO
  - Tokenizer
  - XML

## 🚀 Instalación

1. Clonar el repositorio:
```bash
git clone https://github.com/Alfrevalve/Gesvitalpro.git
cd gesbio
```

2. Instalar dependencias PHP:
```bash
composer install
```

3. Instalar dependencias JavaScript:
```bash
npm install
```

4. Configurar el entorno:
```bash
cp .env.example .env
php artisan key:generate
```

5. Configurar la base de datos en el archivo .env:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gesbio
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
```

6. Ejecutar migraciones y seeders:
```bash
php artisan migrate --seed
```

7. Generar assets:
```bash
npm run build
```

## 💻 Uso

1. Iniciar el servidor de desarrollo:
```bash
php artisan serve
```

2. Compilar assets en tiempo real:
```bash
npm run dev
```

3. Acceder al sistema:
- URL: `http://localhost:8000`
- Credenciales por defecto:
  - Email: admin@gesbio.com
  - Password: password

## 📁 Estructura del Proyecto

```
gesbio/
├── app/
│   ├── Console/Commands/    # Comandos personalizados
│   ├── Http/Controllers/    # Controladores
│   ├── Models/             # Modelos Eloquent
│   ├── Services/           # Servicios de la aplicación
│   └── Providers/          # Service Providers
├── config/                 # Archivos de configuración
├── database/
│   ├── migrations/         # Migraciones
│   └── seeders/           # Seeders
├── resources/
│   ├── css/               # Estilos
│   ├── js/                # JavaScript
│   └── views/             # Vistas Blade
└── routes/                # Definición de rutas
```

## ⚡ Comandos Útiles de Artisan

```bash
# Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Mantenimiento
php artisan down    # Modo mantenimiento
php artisan up      # Desactivar modo mantenimiento

# Base de datos
php artisan migrate:fresh --seed    # Recrear base de datos
php artisan db:seed                 # Ejecutar seeders

# Crear nuevos elementos
php artisan make:model NuevoModelo -mcr    # Modelo + Migración + Controlador
php artisan make:controller NuevoController # Nuevo controlador
```

## 🚀 Despliegue

### Requisitos del Servidor
- PHP >= 8.1
- Composer
- MySQL >= 8.0
- Nginx o Apache
- SSL Certificate

### Pasos de Despliegue

1. Configurar el servidor web:

```nginx
# Nginx
server {
    listen 80;
    server_name tu-dominio.com;
    root /var/www/gesbio/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

2. Optimizar para producción:
```bash
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 👥 Contribución

1. Hacer fork del repositorio
2. Crear una nueva rama:
```bash
git checkout -b feature/nueva-caracteristica
```
3. Realizar cambios y commit:
```bash
git commit -am 'feat: agregar nueva característica'
```
4. Push a la rama:
```bash
git push origin feature/nueva-caracteristica
```
5. Crear Pull Request

### Convenciones de Código

- Seguir PSR-12
- Documentar métodos y clases
- Escribir pruebas para nuevas características
- Seguir convenciones de nombres de Laravel

## 📄 Licencia

Este proyecto está bajo la Licencia MIT - ver el archivo [LICENSE](LICENSE) para más detalles.

## 📞 Soporte

Para reportar bugs o solicitar nuevas características, por favor:

1. Revisar los [Issues existentes](https://github.com/Alfrevalve/Gesvitalpro/issues)
2. Crear un nuevo Issue con toda la información relevante:
   - Descripción detallada del problema
   - Pasos para reproducir
   - Comportamiento esperado
   - Screenshots si aplica
