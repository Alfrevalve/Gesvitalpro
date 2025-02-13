<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class OptimizePerformance
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Establecer límites de memoria según la configuración
        $this->setMemoryLimits();

        // Optimizar la respuesta
        $response = $next($request);

        // Aplicar optimizaciones a la respuesta
        return $this->optimizeResponse($response);
    }

    /**
     * Establecer límites de memoria
     */
    protected function setMemoryLimits(): void
    {
        $memoryLimit = config('optimization.memory.default');
        
        // Aumentar límite para operaciones específicas
        if ($this->isResourceIntensiveOperation()) {
            $memoryLimit = config('optimization.memory.max');
        }

        ini_set('memory_limit', $memoryLimit);
        ini_set('max_execution_time', config('optimization.performance.max_execution_time'));
    }

    /**
     * Verificar si es una operación que requiere más recursos
     */
    protected function isResourceIntensiveOperation(): bool
    {
        $intensiveRoutes = [
            'dashboard/*',
            'reportes/*',
            'exportar/*',
            'importar/*'
        ];

        foreach ($intensiveRoutes as $route) {
            if (request()->is($route)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Optimizar la respuesta HTTP
     */
    protected function optimizeResponse(Response $response): Response
    {
        // No optimizar respuestas binarias o de streaming
        if ($this->shouldSkipOptimization($response)) {
            return $response;
        }

        // Comprimir contenido si es posible
        if ($this->shouldCompressContent($response)) {
            $this->compressContent($response);
        }

        // Agregar headers de caché si corresponde
        if ($this->shouldCache($response)) {
            $this->addCacheHeaders($response);
        }

        // Optimizar HTML
        if ($this->isHtmlResponse($response)) {
            $this->optimizeHtml($response);
        }

        return $response;
    }

    /**
     * Verificar si se debe omitir la optimización
     */
    protected function shouldSkipOptimization(Response $response): bool
    {
        return $response->headers->has('Content-Disposition') ||
               $response->headers->has('Content-Transfer-Encoding') ||
               $response->isRedirection();
    }

    /**
     * Verificar si se debe comprimir el contenido
     */
    protected function shouldCompressContent(Response $response): bool
    {
        return !$response->headers->has('Content-Encoding') &&
               $response->headers->get('Content-Type') !== 'application/pdf' &&
               $response->getContent() &&
               strlen($response->getContent()) > 1024;
    }

    /**
     * Comprimir el contenido de la respuesta
     */
    protected function compressContent(Response $response): void
    {
        if (extension_loaded('zlib')) {
            $content = $response->getContent();
            $compressed = gzencode($content, 9);
            
            if ($compressed !== false) {
                $response->setContent($compressed);
                $response->headers->set('Content-Encoding', 'gzip');
            }
        }
    }

    /**
     * Verificar si la respuesta debe ser cacheada
     */
    protected function shouldCache(Response $response): bool
    {
        return !$response->headers->hasCacheControlDirective('no-cache') &&
               !$response->headers->getCacheControlDirective('private') &&
               request()->isMethod('GET') &&
               auth()->guest();
    }

    /**
     * Agregar headers de caché
     */
    protected function addCacheHeaders(Response $response): void
    {
        $ttl = config('optimization.cache.ttl');
        
        $response->headers->set('Cache-Control', 'public, max-age=' . $ttl);
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Expires', gmdate('D, d M Y H:i:s', time() + $ttl) . ' GMT');
    }

    /**
     * Verificar si es una respuesta HTML
     */
    protected function isHtmlResponse(Response $response): bool
    {
        $contentType = $response->headers->get('Content-Type');
        return $contentType && strpos($contentType, 'text/html') !== false;
    }

    /**
     * Optimizar contenido HTML
     */
    protected function optimizeHtml(Response $response): void
    {
        $content = $response->getContent();
        
        if ($content) {
            // Eliminar espacios en blanco y comentarios innecesarios
            $content = preg_replace('/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $content);
            $content = preg_replace('/\s+/', ' ', $content);
            
            // Minimizar HTML
            $content = str_replace(["\n", "\r", "\t"], '', $content);
            
            $response->setContent($content);
        }
    }
}
