<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SimpleOptimizeResponse
{
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

        if (!$this->shouldOptimize($request, $response)) {
            return $response;
        }

        $content = $response->getContent();

        // Recolectar scripts
        $deferredScripts = [];
        $content = preg_replace_callback(
            '/<script(?!.*?\b(critical|async|defer)\b)[^>]*>.*?<\/script>/is',
            function($matches) use (&$deferredScripts) {
                $deferredScripts[] = $matches[0];
                return '';
            },
            $content
        );

        // Mover scripts al final del body
        if (!empty($deferredScripts)) {
            $content = str_replace(
                '</body>',
                implode("\n", $deferredScripts) . '</body>',
                $content
            );
        }

        // Optimizar imágenes
        $content = preg_replace(
            '/<img(?!.*?\bloading=)[^>]*>/i',
            '<img loading="lazy" $0',
            $content
        );

        // Establecer el contenido optimizado
        $response->setContent($content);

        // Establecer headers de optimización
        $this->setOptimizationHeaders($response);

        return $response;
    }

    /**
     * Determinar si la respuesta debe ser optimizada
     */
    protected function shouldOptimize(Request $request, $response): bool
    {
        if (!$response instanceof Response) {
            return false;
        }

        if ($request->ajax()) {
            return false;
        }

        $contentType = $response->headers->get('Content-Type');
        return $contentType && str_contains($contentType, 'text/html');
    }

    /**
     * Establecer headers de optimización
     */
    protected function setOptimizationHeaders($response): void
    {
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        if (!app()->environment('local')) {
            $response->headers->set('Cache-Control', 'public, max-age=31536000');
        }
    }
}
