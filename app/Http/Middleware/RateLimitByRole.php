<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RateLimitByRole
{
    protected function getRateLimit($user): array
    {
        if ($user->isAdmin()) {
            return [
                'attempts' => 100,
                'decay' => 1, // minuto
            ];
        }

        if ($user->isGerente()) {
            return [
                'attempts' => 60,
                'decay' => 1,
            ];
        }

        return [
            'attempts' => 30,
            'decay' => 1,
        ];
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        $limits = $this->getRateLimit($user);

        $key = 'rate_limit:'.$user->id;

        if (RateLimiter::tooManyAttempts($key, $limits['attempts'])) {
            return response()->json([
                'message' => 'Demasiadas solicitudes. Por favor, espere antes de realizar más peticiones.',
            ], 429);
        }

        RateLimiter::hit($key, $limits['decay'] * 60);

        return $next($request);
    }
}
