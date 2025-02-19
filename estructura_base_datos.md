# 🚀 Estructura de la Base de Datos - Sistema de Gestión Quirúrgica

## 📊 Tablas Principales

### 1️⃣ users (Usuarios del Sistema)
- `id` (BIGINT, PRIMARY KEY, AUTO_INCREMENT)
- `name` (VARCHAR(255))
- `email` (VARCHAR(255), UNIQUE)
- `password` (VARCHAR(255))
- `line_id` (BIGINT, FOREIGN KEY -> lines.id, NULLABLE)
- `email_verified_at` (TIMESTAMP, NULLABLE)
- `remember_token` (VARCHAR(100), NULLABLE)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)
- `deleted_at` (TIMESTAMP, NULLABLE)

### 2️⃣ activity_logs (Registro de Actividades)
- `id` (BIGINT, PRIMARY KEY, AUTO_INCREMENT)
- `action` (VARCHAR(255))
- `model_type` (VARCHAR(255))
- `model_id` (BIGINT)
- `user_id` (BIGINT, FOREIGN KEY -> users.id)
- `changes` (JSON)
- `original` (JSON)
- `ip_address` (VARCHAR(45))
- `user_agent` (TEXT)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)

### 3️⃣ surgeries (Cirugías)
- `id` (BIGINT, PRIMARY KEY, AUTO_INCREMENT)
- `line_id` (BIGINT, FOREIGN KEY -> lines.id)
- `institucion_id` (BIGINT, FOREIGN KEY -> instituciones.id)
- `medico_id` (BIGINT, FOREIGN KEY -> medicos.id)
- `patient_name` (VARCHAR(255))
- `surgery_type` (VARCHAR(255))
- `surgery_date` (DATETIME)
- `admission_date` (DATETIME)
- `description` (TEXT, NULLABLE)
- `notes` (TEXT, NULLABLE)
- `status` (ENUM: 'pending', 'in_progress', 'completed', 'cancelled', 'rescheduled')
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)
- `deleted_at` (TIMESTAMP, NULLABLE)

### 4️⃣ equipment (Equipamiento)
- `id` (BIGINT, PRIMARY KEY, AUTO_INCREMENT)
- `name` (VARCHAR(255))
- `description` (TEXT, NULLABLE)
- `serial_number` (VARCHAR(255), UNIQUE)
- `status` (ENUM: 'available', 'in_use', 'maintenance')
- `line_id` (BIGINT, FOREIGN KEY -> lines.id)
- `next_maintenance_date` (DATE, NULLABLE)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)
- `deleted_at` (TIMESTAMP, NULLABLE)

### 5️⃣ lines (Líneas de Negocio)
- `id` (BIGINT, PRIMARY KEY, AUTO_INCREMENT)
- `name` (VARCHAR(255), UNIQUE)
- `description` (TEXT, NULLABLE)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)
- `deleted_at` (TIMESTAMP, NULLABLE)

### 6️⃣ instituciones (Instituciones Médicas)
- `id` (BIGINT, PRIMARY KEY, AUTO_INCREMENT)
- `nombre` (VARCHAR(255))
- `codigo_renipress` (VARCHAR(255), UNIQUE)
- `tipo_establecimiento` (VARCHAR(255))
- `categoria` (VARCHAR(255))
- `red_salud` (VARCHAR(255))
- `latitud` (DECIMAL(10,8))
- `longitud` (DECIMAL(11,8))
- `datos_ubicacion` (JSON)
- `telefono` (VARCHAR(255))
- `email` (VARCHAR(255))
- `direccion` (TEXT)
- `ciudad` (VARCHAR(255))
- `estado` (VARCHAR(255))
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)
- `deleted_at` (TIMESTAMP, NULLABLE)

### 7️⃣ medicos (Médicos)
- `id` (BIGINT, PRIMARY KEY, AUTO_INCREMENT)
- `nombre` (VARCHAR(255))
- `especialidad` (VARCHAR(255))
- `email` (VARCHAR(255), UNIQUE)
- `telefono` (VARCHAR(255))
- `estado` (VARCHAR(255))
- `institucion_id` (BIGINT, FOREIGN KEY -> instituciones.id)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)

### 8️⃣ storage_processes (Procesos de Almacén)
- `id` (BIGINT, PRIMARY KEY, AUTO_INCREMENT)
- `surgery_request_id` (BIGINT, FOREIGN KEY -> surgery_requests.id)
- `status` (ENUM: 'pending', 'in_progress', 'completed')
- `priority` (ENUM: 'low', 'medium', 'high')
- `notes` (TEXT, NULLABLE)
- `prepared_by` (BIGINT, FOREIGN KEY -> users.id)
- `completed_at` (TIMESTAMP, NULLABLE)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)
- `deleted_at` (TIMESTAMP, NULLABLE)

### 9️⃣ surgery_requests (Solicitudes de Cirugía)
- `id` (BIGINT, PRIMARY KEY, AUTO_INCREMENT)
- `surgery_id` (BIGINT, FOREIGN KEY -> surgeries.id)
- `status` (ENUM: 'pending', 'in_progress', 'completed')
- `notes` (TEXT, NULLABLE)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)
- `deleted_at` (TIMESTAMP, NULLABLE)

### 🔟 zonas (Zonas Geográficas)
- `id` (BIGINT, PRIMARY KEY, AUTO_INCREMENT)
- `nombre` (VARCHAR(255))
- `color` (VARCHAR(255))
- `poligono` (JSON)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)

### 1️⃣1️⃣ externos (Personal Externo)
- `id` (BIGINT, PRIMARY KEY, AUTO_INCREMENT)
- `nombre` (VARCHAR(255))
- `email` (VARCHAR(255), UNIQUE)
- `telefono` (VARCHAR(255))
- `especialidad` (VARCHAR(255))
- `institucion_id` (BIGINT, FOREIGN KEY -> instituciones.id)
- `notas` (TEXT, NULLABLE)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)
- `deleted_at` (TIMESTAMP, NULLABLE)

### 1️⃣2️⃣ visitas (Visitas)
- `id` (BIGINT, PRIMARY KEY, AUTO_INCREMENT)
- `user_id` (BIGINT, FOREIGN KEY -> users.id)
- `institucion_id` (BIGINT, FOREIGN KEY -> instituciones.id)
- `medico_id` (BIGINT, FOREIGN KEY -> medicos.id)
- `fecha` (DATETIME)
- `motivo` (TEXT)
- `observaciones` (TEXT, NULLABLE)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)

### 1️⃣3️⃣ roles (Roles)
- `id` (BIGINT, PRIMARY KEY, AUTO_INCREMENT)
- `name` (VARCHAR(255))
- `slug` (VARCHAR(255), UNIQUE)
- `description` (TEXT, NULLABLE)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)
- `deleted_at` (TIMESTAMP, NULLABLE)

### 1️⃣4️⃣ permissions (Permisos)
- `id` (BIGINT, PRIMARY KEY, AUTO_INCREMENT)
- `name` (VARCHAR(255))
- `slug` (VARCHAR(255), UNIQUE)
- `description` (TEXT, NULLABLE)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)

### 1️⃣5️⃣ surgery_materials (Materiales de Cirugía)
- `id` (BIGINT, PRIMARY KEY, AUTO_INCREMENT)
- `surgery_id` (BIGINT, FOREIGN KEY -> surgeries.id)
- `material_name` (VARCHAR(255))
- `quantity` (INTEGER)
- `status` (ENUM: 'Pendiente', 'Preparado', 'Despachado')
- `notes` (TEXT, NULLABLE)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)
- `deleted_at` (TIMESTAMP, NULLABLE)

### 1️⃣6️⃣ dispatch_processes (Procesos de Despacho)
- `id` (BIGINT, PRIMARY KEY, AUTO_INCREMENT)
- `storage_process_id` (BIGINT, FOREIGN KEY -> storage_processes.id)
- `dispatched_by` (BIGINT, FOREIGN KEY -> users.id)
- `status` (ENUM: 'pending', 'in_progress', 'completed')
- `dispatched_at` (TIMESTAMP, NULLABLE)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)
- `deleted_at` (TIMESTAMP, NULLABLE)

## 🔄 Tablas Pivote

### 1️⃣ role_user (Roles de Usuario)
- `role_id` (BIGINT, FOREIGN KEY -> roles.id)
- `user_id` (BIGINT, FOREIGN KEY -> users.id)
- PRIMARY KEY (`role_id`, `user_id`)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)

### 2️⃣ permission_role (Permisos por Rol)
- `permission_id` (BIGINT, FOREIGN KEY -> permissions.id)
- `role_id` (BIGINT, FOREIGN KEY -> roles.id)
- PRIMARY KEY (`permission_id`, `role_id`)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)

### 3️⃣ surgery_equipment (Equipos por Cirugía)
- `surgery_id` (BIGINT, FOREIGN KEY -> surgeries.id)
- `equipment_id` (BIGINT, FOREIGN KEY -> equipment.id)
- PRIMARY KEY (`surgery_id`, `equipment_id`)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)

### 4️⃣ surgery_staff (Personal por Cirugía)
- `surgery_id` (BIGINT, FOREIGN KEY -> surgeries.id)
- `user_id` (BIGINT, FOREIGN KEY -> users.id)
- PRIMARY KEY (`surgery_id`, `user_id`)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)

### 5️⃣ line_staff (Personal por Línea)
- `line_id` (BIGINT, FOREIGN KEY -> lines.id)
- `user_id` (BIGINT, FOREIGN KEY -> users.id)
- `role` (VARCHAR(255))
- PRIMARY KEY (`line_id`, `user_id`)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)

### 6️⃣ institucion_line (Instituciones por Línea)
- `institucion_id` (BIGINT, FOREIGN KEY -> instituciones.id)
- `line_id` (BIGINT, FOREIGN KEY -> lines.id)
- PRIMARY KEY (`institucion_id`, `line_id`)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)

### 7️⃣ zona_institucion (Zonas por Institución)
- `zona_id` (BIGINT, FOREIGN KEY -> zonas.id)
- `institucion_id` (BIGINT, FOREIGN KEY -> instituciones.id)
- PRIMARY KEY (`zona_id`, `institucion_id`)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)

### 8️⃣ visita_externo (Externos por Visita)
- `visita_id` (BIGINT, FOREIGN KEY -> visitas.id)
- `externo_id` (BIGINT, FOREIGN KEY -> externos.id)
- PRIMARY KEY (`visita_id`, `externo_id`)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)

### 9️⃣ surgery_externo (Externos por Cirugía)
- `surgery_id` (BIGINT, FOREIGN KEY -> surgeries.id)
- `externo_id` (BIGINT, FOREIGN KEY -> externos.id)
- PRIMARY KEY (`surgery_id`, `externo_id`)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)

## 📈 Índices Recomendados

### Users
- `email` (UNIQUE)
- `line_id` (INDEX)

### Surgeries
- `line_id` (INDEX)
- `institucion_id` (INDEX)
- `medico_id` (INDEX)
- `status` (INDEX)
- `surgery_date` (INDEX)
- Índice compuesto: (`status`, `surgery_date`)

### Equipment
- `serial_number` (UNIQUE)
- `line_id` (INDEX)
- `status` (INDEX)
- Índice compuesto: (`line_id`, `status`)

### Instituciones
- `codigo_renipress` (UNIQUE)
- `tipo_establecimiento` (INDEX)
- `ciudad` (INDEX)

### Storage Processes
- `surgery_request_id` (INDEX)
- `status` (INDEX)
- `priority` (INDEX)
- `prepared_by` (INDEX)

### Activity Logs
- Índice compuesto: (`model_type`, `model_id`)
- `user_id` (INDEX)
- `created_at` (INDEX)

### Zonas
- `nombre` (INDEX)

### Externos
- `email` (UNIQUE)
- `institucion_id` (INDEX)
- `especialidad` (INDEX)

## 🔒 Restricciones y Validaciones

1. Integridad Referencial
   - ON DELETE CASCADE para `surgery_equipment` y `surgery_staff`
   - ON DELETE RESTRICT para relaciones críticas como `line_id` en `equipment`

2. Validaciones de Estado
   - CHECK constraints para estados válidos en `surgeries.status`
   - CHECK constraints para estados válidos en `equipment.status`
   - CHECK constraints para prioridad válida en `storage_processes.priority`

3. Validaciones de Datos
   - NOT NULL para campos críticos como `email`, `name`
   - CHECK constraints para validar formatos de email
   - CHECK constraints para validar coordenadas geográficas

## 🔍 Optimizaciones Recomendadas

1. Particionamiento
   - Particionar `surgeries` por fecha para mejorar consultas históricas
   - Particionar `activity_logs` por fecha para optimizar consultas de auditoría
   - Particionar `storage_processes` por estado para optimizar consultas de procesos activos

2. Materialización de Vistas
   - Vista materializada para estadísticas de cirugías por línea
   - Vista materializada para disponibilidad de equipos
   - Vista materializada para métricas de actividad por zona geográfica

3. Caché
   - Implementar caché para consultas frecuentes de equipos disponibles
   - Caché para listados de personal por línea
   - Caché para zonas geográficas y sus instituciones asociadas

4. Archivado
   - Implementar política de archivado para registros antiguos
   - Mantener datos históricos en tablas separadas

## 📊 Diagrama ERD

```mermaid
erDiagram
    USERS ||--o{ SURGERIES : "manages"
    USERS ||--o{ LINE_STAFF : "belongs_to"
    USERS ||--o{ ACTIVITY_LOGS : "generates"
    LINES ||--o{ EQUIPMENT : "owns"
    LINES ||--o{ LINE_STAFF : "has"
    LINES ||--o{ SURGERIES : "manages"
    INSTITUCIONES ||--o{ MEDICOS : "employs"
    INSTITUCIONES ||--o{ SURGERIES : "hosts"
    INSTITUCIONES ||--o{ EXTERNOS : "associates"
    MEDICOS ||--o{ SURGERIES : "performs"
    MEDICOS ||--o{ VISITAS : "receives"
    SURGERIES ||--o{ SURGERY_EQUIPMENT : "uses"
    EQUIPMENT ||--o{ SURGERY_EQUIPMENT : "assigned_to"
    SURGERIES ||--o{ SURGERY_STAFF : "staffed_by"
    SURGERIES ||--o{ SURGERY_MATERIALS : "requires"
    USERS ||--o{ SURGERY_STAFF : "participates"
    SURGERIES ||--o{ SURGERY_REQUESTS : "generates"
    SURGERY_REQUESTS ||--o{ STORAGE_PROCESSES : "requires"
    STORAGE_PROCESSES ||--o{ DISPATCH_PROCESSES : "initiates"
    ZONAS ||--o{ ZONA_INSTITUCION : "contains"
    INSTITUCIONES ||--o{ ZONA_INSTITUCION : "belongs_to"
    ROLES ||--o{ ROLE_USER : "assigned_to"
    USERS ||--o{ ROLE_USER : "has"
    PERMISSIONS ||--o{ PERMISSION_ROLE : "granted_to"
    ROLES ||--o{ PERMISSION_ROLE : "has"
    EXTERNOS ||--o{ VISITA_EXTERNO : "participates"
    VISITAS ||--o{ VISITA_EXTERNO : "includes"
    EXTERNOS ||--o{ SURGERY_EXTERNO : "assists"
    SURGERIES ||--o{ SURGERY_EXTERNO : "includes"
