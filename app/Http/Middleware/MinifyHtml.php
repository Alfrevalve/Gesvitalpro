<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class MinifyHtml
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

        if ($this->shouldMinify($request, $response)) {
            $this->minify($response);
        }

        return $response;
    }

    /**
     * Determinar si el contenido debe ser minificado
     */
    protected function shouldMinify(Request $request, $response): bool
    {
        if (!$response instanceof Response) {
            return false;
        }

        // Solo minificar HTML
        $contentType = $response->headers->get('Content-Type');
        if (!$contentType || !str_contains($contentType, 'text/html')) {
            return false;
        }

        // No minificar para solicitudes AJAX
        if ($request->ajax()) {
            return false;
        }

        // No minificar en modo debug
        if (config('app.debug')) {
            return false;
        }

        return true;
    }

    /**
     * Minificar el contenido HTML
     */
    protected function minify($response): void
    {
        $content = $response->getContent();

        // Preservar contenido de scripts y estilos
        $content = preg_replace_callback(
            '/<(script|style)[^>]*>.*?<\/\1>/si',
            function($matches) {
                return $this->preserveContent($matches[0]);
            },
            $content
        );

        // Remover comentarios HTML (excepto IE conditionals)
        $content = preg_replace(
            '/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s',
            '',
            $content
        );

        // Remover espacios en blanco innecesarios
        $rules = [
            '/\>[^\S ]+/s'                                                    => '>',     // Espacios después de >
            '/[^\S ]+\</s'                                                    => '<',     // Espacios antes de <
            '/(\s)+/s'                                                        => '\\1',   // Múltiples espacios en uno
            '/\s+([^<>\s]+=([\'"])[^\'"]+\2)/s'                             => '$1',    // Espacios alrededor de atributos
            '/\s+>/s'                                                        => '>',     // Espacios antes de cierre
            '/\s+\/>/s'                                                      => '/>',    // Espacios antes de cierre auto
            '/\s+\n/s'                                                       => "\n",    // Espacios al final de línea
            '/\n+/s'                                                         => "\n",    // Múltiples líneas en una
            '/\s+/s'                                                         => ' ',     // Múltiples espacios en uno
        ];

        $content = preg_replace(array_keys($rules), array_values($rules), $content);

        // Restaurar contenido preservado
        $content = $this->restorePreservedContent($content);

        // Minificar JavaScript inline
        $content = preg_replace_callback(
            '/<script[^>]*>(.*?)<\/script>/si',
            function($matches) {
                return $this->minifyJavaScript($matches[0]);
            },
            $content
        );

        // Minificar CSS inline
        $content = preg_replace_callback(
            '/<style[^>]*>(.*?)<\/style>/si',
            function($matches) {
                return $this->minifyCSS($matches[0]);
            },
            $content
        );

        $response->setContent(trim($content));
    }

    /**
     * Preservar contenido que no debe ser modificado
     */
    protected function preserveContent(string $content): string
    {
        static $i = 0;
        $placeholder = '<!--PRESERVE' . (++$i) . '-->';
        $this->preserved[$placeholder] = $content;
        return $placeholder;
    }

    /**
     * Restaurar contenido preservado
     */
    protected function restorePreservedContent(string $content): string
    {
        if (!empty($this->preserved)) {
            $content = str_replace(
                array_keys($this->preserved),
                array_values($this->preserved),
                $content
            );
        }
        return $content;
    }

    /**
     * Minificar JavaScript
     */
    protected function minifyJavaScript(string $script): string
    {
        // Extraer contenido del script
        preg_match('/<script[^>]*>(.*?)<\/script>/si', $script, $matches);
        $content = $matches[1];

        // Reglas básicas de minificación JS
        $content = preg_replace('/\/\/[^\n]*/', '', $content); // Remover comentarios de línea
        $content = preg_replace('/\/\*.*?\*\//s', '', $content); // Remover comentarios multi-línea
        $content = preg_replace('/\s+/', ' ', $content); // Reducir espacios múltiples
        $content = preg_replace('/\s*([=\{\}\[\];,<>+\-\*\/])\s*/', '$1', $content); // Remover espacios alrededor de operadores

        return str_replace($matches[1], trim($content), $script);
    }

    /**
     * Minificar CSS
     */
    protected function minifyCSS(string $style): string
    {
        // Extraer contenido del estilo
        preg_match('/<style[^>]*>(.*?)<\/style>/si', $style, $matches);
        $content = $matches[1];

        // Reglas básicas de minificación CSS
        $content = preg_replace('/\/\*.*?\*\//s', '', $content); // Remover comentarios
        $content = preg_replace('/\s+/', ' ', $content); // Reducir espacios múltiples
        $content = preg_replace('/\s*([\{\}:;,])\s*/', '$1', $content); // Remover espacios alrededor de símbolos
        $content = preg_replace('/([\{;])[^\S ]+/s', '$1', $content); // Remover espacios después de { y ;
        $content = preg_replace('/\s+(!important)/', '$1', $content); // Remover espacios antes de !important

        return str_replace($matches[1], trim($content), $style);
    }
}
