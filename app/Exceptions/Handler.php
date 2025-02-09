<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        // Add the exception types that you do not want to report
    ];

    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

public function render($request, Exception $exception)
    {
        return parent::render($request, $exception);
    }
}
