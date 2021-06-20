<?php

namespace App\Exceptions;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
        });
    }

    /**
     * @param Request   $request
     * @param Throwable $e
     *
     * @return Response
     *
     * @throws BindingResolutionException
     * @throws Throwable
     */
    public function render($request, Throwable $e)
    {
        if ($request->expectsJson() && $e instanceof HttpException) {
            return response()
                ->json(['message' => $e->getMessage()], $e->getStatusCode());
        }

        return parent::render($request, $e);
    }
}
