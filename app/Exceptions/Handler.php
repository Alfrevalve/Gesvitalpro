<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        // Aquí puedes agregar excepciones que no deseas reportar
    ];

    public function render($request, Exception $exception)
    {
        if ($request->expectsJson()) {
            return new JsonResponse([
                'error' => 'Error en la solicitud',
                'message' => $exception->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }

        return parent::render($request, $exception);
    }
}
