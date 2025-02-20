<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateErrorIllustrations extends Command
{
    protected $signature = 'errors:generate-illustrations';
    protected $description = 'Genera las ilustraciones SVG para las páginas de error';

    protected $illustrations = [
        'bad-request' => [
            'color' => '#dc3545',
            'icon' => 'exclamation-circle',
            'code' => '400'
        ],
        'unauthorized' => [
            'color' => '#ffc107',
            'icon' => 'lock',
            'code' => '401'
        ],
        'forbidden' => [
            'color' => '#dc3545',
            'icon' => 'ban',
            'code' => '403'
        ],
        'not-found' => [
            'color' => '#007bff',
            'icon' => 'search',
            'code' => '404'
        ],
        'server-error' => [
            'color' => '#dc3545',
            'icon' => 'server',
            'code' => '500'
        ],
        'service-unavailable' => [
            'color' => '#ffc107',
            'icon' => 'clock',
            'code' => '503'
        ]
    ];

    public function handle()
    {
        $this->info('Generando ilustraciones para páginas de error...');

        // Crear directorio si no existe
        $directory = public_path('img/errors');
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        foreach ($this->illustrations as $name => $config) {
            $this->generateIllustration($name, $config);
        }

        $this->info('¡Ilustraciones generadas exitosamente!');
    }

    protected function generateIllustration($name, $config)
    {
        $svgTemplate = $this->getSvgTemplate($config);
        $filePath = public_path("img/errors/{$name}.svg");

        File::put($filePath, $svgTemplate);
        $this->line("Generada ilustración: {$name}.svg");
    }

    protected function getSvgTemplate($config)
    {
        return <<<SVG
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="400" height="300" viewBox="0 0 400 300">
    <style>
        @media (prefers-color-scheme: dark) {
            #background { fill: #343a40; }
            .text { fill: #ffffff; }
        }
        @media (prefers-color-scheme: light) {
            #background { fill: #f8f9fa; }
            .text { fill: #212529; }
        }
        .accent { fill: {$config['color']}; }
        .icon { font-family: 'Font Awesome 5 Free'; font-weight: 900; }
    </style>

    <!-- Fondo -->
    <rect id="background" width="400" height="300" rx="10"/>

    <!-- Círculo central -->
    <circle class="accent" cx="200" cy="120" r="60" opacity="0.1"/>

    <!-- Icono -->
    <text class="accent icon" x="200" y="140" text-anchor="middle" font-size="48">
        &#x{$this->getIconUnicode($config['icon'])};
    </text>

    <!-- Código de error -->
    <text class="text" x="200" y="220" text-anchor="middle" font-size="36" font-weight="bold">
        {$config['code']}
    </text>

    <!-- Elementos decorativos -->
    <g class="accent" opacity="0.1">
        <circle cx="50" cy="50" r="20"/>
        <circle cx="350" cy="250" r="15"/>
        <circle cx="30" cy="250" r="10"/>
        <circle cx="350" cy="50" r="25"/>
    </g>
</svg>
SVG;
    }

    protected function getIconUnicode($icon)
    {
        // Mapeo de iconos a códigos Unicode de Font Awesome
        $iconMap = [
            'exclamation-circle' => 'f06a',
            'lock' => 'f023',
            'ban' => 'f05e',
            'search' => 'f002',
            'server' => 'f233',
            'clock' => 'f017'
        ];

        return $iconMap[$icon] ?? 'f06a';
    }
}
