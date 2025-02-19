<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];

    /**
     * Determine if the request has a valid CSRF token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function tokensMatch($request)
    {
        // En desarrollo local, ser más permisivo con la validación CSRF
        if (config('app.env') === 'local') {
            $token = $request->session()->token();
            $header = $request->header('X-CSRF-TOKEN');
            $input = $request->input('_token');

            return is_string($token) &&
                   (hash_equals($token, (string) $header) ||
                    hash_equals($token, (string) $input));
        }

        return parent::tokensMatch($request);
    }

    /**
     * Add the CSRF token to the response headers.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Symfony\Component\HttpFoundation\Response  $response
     * @return void
     */
    protected function addCookieToResponse($request, $response)
    {
        $config = config('session');

        $response->headers->setCookie(
            new \Symfony\Component\HttpFoundation\Cookie(
                'XSRF-TOKEN',
                $request->session()->token(),
                time() + 60 * $config['lifetime'],
                $config['path'],
                $config['domain'],
                $config['secure'],
                $config['http_only'],
                false,
                $config['same_site'] ?? 'lax'
            )
        );
    }
}
