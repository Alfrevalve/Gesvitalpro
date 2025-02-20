<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Services\UiOptimizationService;

class ErrorPage extends Component
{
    public $code;
    public $message;
    public $description;
    public $illustration;
    protected $uiOptimizer;

    public function __construct(
        string $code,
        string $message,
        string $description,
        UiOptimizationService $uiOptimizer
    ) {
        $this->code = $code;
        $this->message = $message;
        $this->description = $description;
        $this->uiOptimizer = $uiOptimizer;
        $this->illustration = $this->getIllustration($code);
    }

    protected function getIllustration(string $code): string
    {
        return match($code) {
            '400' => 'bad-request.svg',
            '401' => 'unauthorized.svg',
            '403' => 'forbidden.svg',
            '404' => 'not-found.svg',
            '500' => 'server-error.svg',
            '503' => 'service-unavailable.svg',
            default => 'error.svg',
        };
    }

    public function render()
    {
        return view('components.error-page', [
            'darkMode' => session('dark_mode', false),
            'animations' => $this->uiOptimizer->getAnimationsConfig(),
        ]);
    }

    public function getErrorData(): array
    {
        return [
            '400' => [
                'title' => 'Bad Request',
                'description' => 'Algo salió mal con la petición. Por favor, verifica los datos enviados.',
            ],
            '401' => [
                'title' => 'No Autorizado',
                'description' => 'No tienes permiso para acceder a este contenido. Por favor, inicia sesión.',
            ],
            '403' => [
                'title' => 'Acceso Prohibido',
                'description' => 'No tienes los permisos necesarios para acceder a este recurso.',
            ],
            '404' => [
                'title' => 'Página No Encontrada',
                'description' => 'La página que estás buscando no existe o ha sido movida.',
            ],
            '500' => [
                'title' => 'Error del Servidor',
                'description' => 'Ha ocurrido un error inesperado. Por favor, intenta más tarde.',
            ],
            '503' => [
                'title' => 'Servicio No Disponible',
                'description' => 'El servidor está temporalmente sobrecargado. Por favor, intenta más tarde.',
            ],
        ];
    }
}
