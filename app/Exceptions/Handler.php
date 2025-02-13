<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            if (app()->bound('sentry')) {
                app('sentry')->captureException($e);
            }
        });

        // Manejar errores de autenticación
        $this->renderable(function (AuthenticationException $e, $request) {
            $this->logError($e); // Log the authentication error
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'No autenticado',
                    'error' => 'unauthenticated'
                ], 401);
            }
            return redirect()->guest(route('login'));
        });

        // Manejar errores de validación
        $this->renderable(function (ValidationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Los datos proporcionados no son válidos',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        // Manejar errores de modelo no encontrado
        $this->renderable(function (ModelNotFoundException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Recurso no encontrado',
                    'error' => 'not_found'
                ], 404);
            }
            return redirect()->back()->with('error', 'Recurso no encontrado');
        });

        // Manejar errores de autorización
        $this->renderable(function (AuthorizationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'No autorizado',
                    'error' => 'unauthorized'
                ], 403);
            }
            return redirect()->back()->with('error', 'No tiene permiso para realizar esta acción');
        });

        // Manejar errores de token CSRF
        $this->renderable(function (TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'La sesión ha expirado',
                    'error' => 'token_mismatch'
                ], 419);
            }
            return redirect()->back()
                ->withInput($request->except('_token'))
                ->with('error', 'La sesión ha expirado. Por favor, intente nuevamente.');
        });
    }

    /**
     * Log detailed error information
     */
    protected function logError(Throwable $exception): void
    {
        $data = [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'previous' => $exception->getPrevious() ? [
                'message' => $exception->getPrevious()->getMessage(),
                'file' => $exception->getPrevious()->getFile(),
                'line' => $exception->getPrevious()->getLine()
            ] : null,
            'request' => [
                'url' => request()->fullUrl(),
                'method' => request()->method(),
                'input' => request()->except(['password', 'password_confirmation']),
                'headers' => request()->headers->all()
            ],
            'user' => auth()->check() ? [
                'id' => auth()->id(),
                'email' => auth()->user()->email
            ] : null
        ];

        Log::error('Application Error', $data);
    }

    /**
     * Convert an authentication exception into a response.
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return $request->expectsJson()
            ? response()->json(['message' => 'No autenticado.'], 401)
            : redirect()->guest($exception->redirectTo() ?? route('login'));
    }

    /**
     * Create a response object from the given validation exception.
     */
    protected function convertValidationExceptionToResponse(ValidationException $e, $request)
    {
        if ($e->response) {
            return $e->response;
        }

        return $request->expectsJson()
            ? response()->json($e->errors(), 422)
            : redirect()->back()
                ->withInput($request->input())
                ->withErrors($e->errors());
    }
}
