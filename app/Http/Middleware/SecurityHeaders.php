<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
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

        // Prevenir que el navegador MIME-sniff la respuesta
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Habilitar la protección XSS del navegador
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Prevenir clickjacking
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Habilitar HSTS (HTTP Strict Transport Security)
        if (app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Content Security Policy
        $cspDirectives = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net",
            "img-src 'self' data: https:",
            "font-src 'self' https://fonts.gstatic.com",
            "connect-src 'self'",
            "media-src 'self'",
            "object-src 'none'",
            "frame-src 'self'",
            "worker-src 'self' blob:",
            "manifest-src 'self'",
            "base-uri 'self'",
            "form-action 'self'",
        ];

        if (!app()->environment('local')) {
            $response->headers->set('Content-Security-Policy', implode('; ', $cspDirectives));
        }

        // Referrer Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions Policy (anteriormente Feature Policy)
        $response->headers->set('Permissions-Policy', 
            'camera=(), microphone=(), geolocation=(), payment=()'
        );

        // Cache Control
        if (!$request->isMethod('GET')) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
        }

        // Prevenir exposición de información sensible
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }
}
