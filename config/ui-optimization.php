<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración de Optimización de UI
    |--------------------------------------------------------------------------
    */

    // Configuración de tema
    'theme' => [
        'dark_mode' => [
            'enabled' => true,
            'auto_switch' => true, // Cambiar automáticamente según hora del día
            'start_time' => '18:00',
            'end_time' => '06:00',
        ],

        'colors' => [
            'primary' => '#007bff',
            'secondary' => '#6c757d',
            'success' => '#28a745',
            'info' => '#17a2b8',
            'warning' => '#ffc107',
            'danger' => '#dc3545',
        ],

        'custom_css' => [
            'enabled' => true,
            'path' => 'css/custom-theme.css',
        ],
    ],

    // Configuración del Dashboard
    'dashboard' => [
        'refresh_interval' => 30, // segundos
        'widgets' => [
            'cirugias_pendientes' => [
                'enabled' => true,
                'position' => 1,
                'refresh_interval' => 60,
            ],
            'equipos_mantenimiento' => [
                'enabled' => true,
                'position' => 2,
                'refresh_interval' => 300,
            ],
            'estadisticas_rendimiento' => [
                'enabled' => true,
                'position' => 3,
                'refresh_interval' => 60,
            ],
            'alertas_sistema' => [
                'enabled' => true,
                'position' => 4,
                'refresh_interval' => 30,
            ],
        ],
    ],

    // Optimización de Carga
    'loading' => [
        'lazy_load_images' => true,
        'preload_components' => true,
        'use_skeleton_loading' => true,
    ],

    // Configuración de Responsive
    'responsive' => [
        'breakpoints' => [
            'xs' => 0,
            'sm' => 576,
            'md' => 768,
            'lg' => 992,
            'xl' => 1200,
            'xxl' => 1400,
        ],
        'sidebar_breakpoint' => 'lg',
        'optimize_tables' => true,
        'mobile_first' => true,
    ],

    // Configuración de Componentes
    'components' => [
        'datatables' => [
            'responsive' => true,
            'page_length' => 25,
            'dom' => '<"d-flex justify-content-between align-items-center"lf>rt<"d-flex justify-content-between align-items-center"ip>',
            'buttons' => ['excel', 'pdf', 'print'],
        ],
        'select2' => [
            'theme' => 'bootstrap-5',
            'responsive' => true,
        ],
        'charts' => [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'theme' => 'light',
        ],
    ],

    // Configuración de Animaciones
    'animations' => [
        'enabled' => true,
        'reduce_motion' => true, // Respetar preferencias de usuario
        'types' => [
            'fade' => true,
            'slide' => true,
            'zoom' => false,
        ],
    ],

    // Accesibilidad
    'accessibility' => [
        'high_contrast' => true,
        'font_size_controls' => true,
        'screen_reader_support' => true,
        'keyboard_navigation' => true,
    ],

    // Optimización de Rendimiento UI
    'performance' => [
        'minify_html' => true,
        'cache_views' => true,
        'optimize_images' => true,
        'use_cdn' => [
            'enabled' => true,
            'providers' => [
                'jquery' => 'https://code.jquery.com/jquery-3.6.0.min.js',
                'bootstrap' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js',
            ],
        ],
    ],
];
