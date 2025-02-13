# Proyecto GesVitalPro

## Descripción
GesVitalPro es una aplicación web desarrollada en Laravel para la gestión integral de cirugías y pacientes, incluyendo seguimiento de visitas, reportes post-cirugía y administración de personal médico.

## Requisitos del Sistema
- PHP >= 8.2
- MySQL >= 5.7
- Composer
- Node.js >= 16
- NPM

## Instalación

1. Clona el repositorio:
   ```bash
   git clone https://github.com/Alfrevalve/gesvitalpro.git
   ```

2. Navega al directorio del proyecto:
   ```bash
   cd gesvitalpro
   ```

3. Instala las dependencias de PHP:
   ```bash
   composer install
   ```

4. Instala las dependencias de Node.js:
   ```bash
   npm install
   ```

5. Copia el archivo de configuración:
   ```bash
   cp .env.example .env
   ```

6. Genera la clave de la aplicación:
   ```bash
   php artisan key:generate
   ```

7. Configura la base de datos en el archivo .env:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=gesvitalpro
   DB_USERNAME=root
   DB_PASSWORD=
   ```

8. Ejecuta las migraciones y seeders:
   ```bash
   php artisan migrate:fresh --seed
   ```

9. Compila los assets:
   ```bash
   npm run build
   ```

## Uso

1. Inicia el servidor de desarrollo:
   ```bash
   php artisan serve
   ```

2. Accede a la aplicación en tu navegador:
   ```
   http://127.0.0.1:8000
   ```

3. Credenciales de administrador por defecto:
   - Email: admin@gesvitalpro.com
   - Contraseña: admin123

## Características Principales
- Gestión de pacientes
- Programación de cirugías
- Seguimiento post-operatorio
- Reportes y estadísticas
- Sistema de autenticación y autorización
- Monitoreo del sistema
- Gestión de personal médico

## Estructura del Proyecto
- `/app` - Lógica principal de la aplicación
- `/config` - Archivos de configuración
- `/database` - Migraciones y seeders
- `/public` - Archivos públicos
- `/resources` - Vistas y assets
- `/routes` - Definición de rutas
- `/tests` - Tests automatizados

## Mantenimiento
- Los logs del sistema se encuentran en `/storage/logs`
- La configuración de monitoreo puede ajustarse en `/config/monitoring.php`
- Los backups se almacenan en `/storage/app/backups`

## Contribuciones
Las contribuciones son bienvenidas. Por favor:
1. Haz fork del repositorio
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## Licencia
Este proyecto está bajo la Licencia MIT.

## Soporte
Para soporte técnico, por favor contacta a:
- Email: alfredoparedes1986@gmail.com
- GitHub: https://github.com/Alfrevalve
