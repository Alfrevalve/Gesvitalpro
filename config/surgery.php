<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Estados de Cirugía
    |--------------------------------------------------------------------------
    |
    | Define los estados posibles para una cirugía y sus configuraciones
    |
    */
    'status' => [
        'programmed' => [
            'name' => 'Programada',
            'color' => 'blue',
            'icon' => 'calendar',
        ],
        'in_progress' => [
            'name' => 'En Proceso',
            'color' => 'green',
            'icon' => 'play',
        ],
        'finished' => [
            'name' => 'Finalizada',
            'color' => 'gray',
            'icon' => 'check',
        ],
        'cancelled' => [
            'name' => 'Cancelada',
            'color' => 'red',
            'icon' => 'x',
        ],
        'rescheduled' => [
            'name' => 'Reprogramada',
            'color' => 'yellow',
            'icon' => 'refresh',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Líneas Quirúrgicas
    |--------------------------------------------------------------------------
    |
    | Configuración de las líneas quirúrgicas y sus equipos asociados
    |
    */
    'lines' => [
        'NX' => [
            'name' => 'Neurocirugía',
            'equipment_types' => [
                'Calibrador Manual',
                'Calibrador Electrónico',
            ],
        ],
        'CR' => [
            'name' => 'Cirugía Reconstructiva',
            'equipment_types' => [
                'Craneotomo',
            ],
        ],
        'SP' => [
            'name' => 'Cirugía de Columna',
            'equipment_types' => [
                'Drill de Alta Velocidad',
            ],
        ],
        'CX' => [
            'name' => 'Cirugía General',
            'equipment_types' => [],  // Por confirmar
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Mantenimiento
    |--------------------------------------------------------------------------
    |
    | Configuraciones relacionadas con el mantenimiento de equipos
    |
    */
    'maintenance' => [
        'warning_threshold' => 7, // Días antes de mostrar advertencia de mantenimiento
        'interval' => [
            'default' => 90, // Días entre mantenimientos por defecto
            'high_usage' => 60, // Días para equipos de uso frecuente
        ],
        'surgeries_threshold' => 50, // Número de cirugías antes de mantenimiento recomendado
    ],

    /*
    |--------------------------------------------------------------------------
    | Notificaciones
    |--------------------------------------------------------------------------
    |
    | Configuración de las notificaciones del sistema
    |
    */
    'notifications' => [
        'channels' => ['mail', 'database'],
        'events' => [
            'surgery_scheduled' => true,
            'surgery_status_changed' => true,
            'equipment_maintenance_due' => true,
            'staff_assigned' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Programación
    |--------------------------------------------------------------------------
    |
    | Configuraciones relacionadas con la programación de cirugías
    |
    */
    'scheduling' => [
        'min_notice' => 4, // Horas mínimas de anticipación
        'max_future_days' => 180, // Días máximos para programar en el futuro
        'working_hours' => [
            'start' => '07:00',
            'end' => '19:00',
        ],
        'time_slots' => 30, // Minutos por slot de tiempo
    ],
];
