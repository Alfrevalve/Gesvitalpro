# Sistema de Gestión Quirúrgica - GesBio

<p align="center">
<img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
</p>

## Descripción del Proyecto

GesBio es un sistema de gestión integral para clínicas y hospitales, desarrollado con Laravel y AdminLTE. El sistema permite:

- Gestión de cirugías y procedimientos
- Control de inventario de materiales quirúrgicos
- Seguimiento de equipos médicos
- Registro de actividades y auditorías
- Gestión de usuarios y permisos

## Características Principales

✅ Gestión completa del ciclo quirúrgico  
✅ Sistema de notificaciones en tiempo real  
✅ Integración con sistemas de geolocalización  
✅ Monitoreo de rendimiento y seguridad  
✅ Optimización de procesos mediante IA  

## Requisitos del Sistema

- PHP 8.1 o superior
- Composer 2.0 o superior
- MySQL 5.7+ o MariaDB 10.3+
- Node.js 16.x o superior
- Redis (opcional para cache y colas)

## Instalación

1. Clonar el repositorio:
```bash
git clone https://github.com/tu-usuario/gesbio.git
```

2. Instalar dependencias:
```bash
composer install
npm install
```

3. Configurar entorno:
```bash
cp .env.example .env
php artisan key:generate
```

4. Ejecutar migraciones:
```bash
php artisan migrate --seed
```

5. Compilar assets:
```bash
npm run build
```

6. Iniciar servidor:
```bash
php artisan serve
```

## Configuración Importante

- Configurar credenciales de base de datos en `.env`
- Definir variables de entorno para notificaciones
- Configurar Redis para cache y colas (opcional)

## Desarrollo

El proyecto sigue las mejores prácticas de Laravel y utiliza:

- **AdminLTE** como framework de UI
- **Laravel Sanctum** para autenticación API
- **Laravel Telescope** para depuración
- **Laravel Horizon** para gestión de colas

## Estado Actual

✅ Auditoría de código completada  
✅ Optimización de rendimiento implementada  
✅ Sistema de notificaciones activo  
✅ Pruebas unitarias en desarrollo  

## Contribuciones

Las contribuciones son bienvenidas. Por favor sigue las [guías de contribución](CONTRIBUTING.md) del proyecto.

## Documentación

La documentación completa del proyecto se encuentra en la carpeta `docs/`, incluyendo:

- [Auditoría de Código](docs/code_audit_2024.md)
- [Estructura de Base de Datos](estructura_base_datos.md)
- [Guía de Contribución](CONTRIBUTING.md)

## Seguridad

Si descubres alguna vulnerabilidad de seguridad, por favor reportala a través de [issues](https://github.com/tu-usuario/gesbio/issues).

## Licencia

Este proyecto está licenciado bajo la [Licencia MIT](https://opensource.org/licenses/MIT).
