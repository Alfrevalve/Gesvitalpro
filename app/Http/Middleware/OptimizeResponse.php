<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\UiOptimizationService;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class OptimizeResponse
{
    protected $uiOptimizer;

    public function __construct(UiOptimizationService $uiOptimizer)
    {
        $this->uiOptimizer = $uiOptimizer;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($this->shouldOptimize($request, $response)) {
            $this->optimize($response);
        }

        return $response;
    }

    /**
     * Determinar si la respuesta debe ser optimizada
     */
    protected function shouldOptimize(Request $request, $response): bool
    {
        // Solo optimizar respuestas HTML
        if (!$response instanceof Response) {
            return false;
        }

        $contentType = $response->headers->get('Content-Type');
        if (!$contentType || !str_contains($contentType, 'text/html')) {
            return false;
        }

        // No optimizar para solicitudes AJAX
        if ($request->ajax()) {
            return false;
        }

        return true;
    }

    /**
     * Optimizar la respuesta
     */
    protected function optimize($response): void
    {
        $content = $response->getContent();

        // Optimizar carga de scripts
        $content = $this->optimizeScripts($content);

        // Optimizar carga de estilos
        $content = $this->optimizeStyles($content);

        // Optimizar imágenes
        $content = $this->optimizeImages($content);

        // Aplicar optimizaciones específicas para móviles
        if ($this->isMobileRequest()) {
            $content = $this->optimizeForMobile($content);
        }

        $response->setContent($content);

        // Configurar headers de caché y optimización
        $this->setOptimizationHeaders($response);
    }

    /**
     * Optimizar scripts
     */
    protected function optimizeScripts(string $content): string
    {
        // Mover scripts no críticos al final del body
        $content = preg_replace(
            '/<script((?!critical).)*?>.*?<\/script>/is',
            '',
            $content,
            -1,
            $count,
            $scripts
        );

        if ($count > 0) {
            $content = str_replace('</body>', $scripts . '</body>', $content);
        }

        // Agregar atributos defer o async según corresponda
        $content = preg_replace(
            '/<script((?!critical|async|defer).)*?>/',
            '<script defer>',
            $content
        );

        return $content;
    }

    /**
     * Optimizar estilos
     */
    protected function optimizeStyles(string $content): string
    {
        // Extraer estilos críticos
        preg_match_all('/<style[^>]*>(.*?)<\/style>/is', $content, $matches);
        $criticalStyles = implode("\n", $matches[1]);

        // Mover estilos críticos al head
        if (!empty($criticalStyles)) {
            $content = preg_replace(
                '/<\/head>/',
                "<style id=\"critical-css\">{$criticalStyles}</style></head>",
                $content,
                1
            );
        }

        // Cargar estilos no críticos de forma asíncrona
        $content = preg_replace(
            '/<link(.(?!critical))*rel="stylesheet"/',
            '<link rel="preload" as="style" onload="this.onload=null;this.rel=\'stylesheet\'"',
            $content
        );

        return $content;
    }

    /**
     * Optimizar imágenes
     */
    protected function optimizeImages(string $content): string
    {
        // Agregar lazy loading a imágenes
        $content = preg_replace(
            '/<img((?!loading).)*?>/',
            '<img loading="lazy"$1>',
            $content
        );

        // Agregar srcset para imágenes responsivas
        $content = preg_replace(
            '/<img([^>]+)src="([^"]+)"([^>]*)>/',
            '<img$1src="$2"$3 srcset="$2 1x, $2 2x">',
            $content
        );

        return $content;
    }

    /**
     * Optimizar para dispositivos móviles
     */
    protected function optimizeForMobile(string $content): string
    {
        // Reducir calidad de imágenes para móviles
        $content = preg_replace(
            '/<img([^>]+)src="([^"]+)\.(jpg|jpeg|png)"([^>]*)>/',
            '<img$1src="$2-mobile.$3"$4>',
            $content
        );

        // Simplificar estructura DOM para móviles
        $content = preg_replace(
            '/<div class="desktop-only">.*?<\/div>/is',
            '',
            $content
        );

        return $content;
    }

    /**
     * Configurar headers de optimización
     */
    protected function setOptimizationHeaders($response): void
    {
        // Configurar cache-control
        $response->headers->set('Cache-Control', 'public, max-age=31536000');

        // Habilitar compresión gzip
        if (extension_loaded('zlib')) {
            $response->headers->set('Content-Encoding', 'gzip');
        }

        // Configurar headers de seguridad
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
    }

    /**
     * Verificar si es una solicitud móvil
     */
    protected function isMobileRequest(): bool
    {
        return preg_match(
            '/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i',
            $_SERVER['HTTP_USER_AGENT']
        );
    }
}
